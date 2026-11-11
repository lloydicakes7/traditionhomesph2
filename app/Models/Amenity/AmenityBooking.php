<?php

namespace App\Models\Amenity;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Concerns\HasSlipReference;
use App\Models\User;
use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Utility\MoneyHelper;
use App\Utility\TimeHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One reservation of one amenity.
 * The booking records the window, who it is for, and where it stands. What it
 * costs is a bill of its own (see \App\Models\Bill\Bill), raised by the office
 * once the reservation is approved, with the amenity's rate for that window and
 * the refundable deposit the officer entered on the line. `deposit_amount` is
 * that deposit, kept on the reservation so refunding or forfeiting it is the
 * reservation's own lifecycle.
 *
 * Moving the booking along (approving, recording payment, completing,
 * cancelling, settling the deposit) is not done here: those are the office's
 * moves, and they live in \App\Actions\Amenities\Bookings.
 */
class AmenityBooking extends Model
{
    use HasSlipReference;

    protected $fillable = [
        'reference',
        'amenity_id',
        'user_id',
        'created_by_admin_id',
        'bill_id',
        'guest_name',
        'starts_at',
        'ends_at',
        'deposit_amount',
        'deposit_status',
        'status',
        'purpose',
        'remarks',
        'approved_at',
        'deposit_settled_at',
        'completed_at',
        'cancelled_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'starts_at'           => 'datetime',
        'ends_at'             => 'datetime',
        'deposit_amount'      => 'decimal:2',
        'approved_at'         => 'datetime',
        'deposit_settled_at'  => 'datetime',
        'completed_at'        => 'datetime',
        'cancelled_at'        => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The facility this reservation holds.
     */
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }

    /**
     * The homeowner account the reservation is for, when it was filed for one.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The officer who entered the reservation, when the office filed it.
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * The bill this reservation was settled through, once it has been raised.
     *
     * A reservation that costs something becomes a receivable when it is
     * approved (or filed by the office, which approves on the spot), so the
     * charge is a collections bill like any other — which is what puts the
     * receipt on the payments ledger and the charge on the account (see
     * \App\Actions\Amenities\Bookings\Concerns\LodgesBookingBill).
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the slip reference, who the booking is for and
     * where it came from — the resident's name, the unit's block and lot, a
     * guest's name, an invoice number.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('reference', 'like', $like)
                ->orWhere('guest_name', 'like', $like)
                ->orWhereHas('bill', function ($query) use ($like) {
                    $query->where('reference', 'like', $like);
                })
                ->orWhereHas('user.resident', function ($query) use ($like) {
                    $query->where('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                })
                ->orWhereHas('user.household.property', function ($query) use ($like) {
                    $query->where('block', 'like', $like)
                        ->orWhere('lot', 'like', $like);
                });
        });
    }

    /**
     * Only the bookings in one status.
     */
    public function scopeWithStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Only the reservations of one resident's portal account.
     */
    public function scopeForResident($query, $residentId)
    {
        if (! $residentId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('user', function ($query) use ($residentId) {
            $query->where('resident_id', $residentId);
        });
    }
    /**
     * Only the bookings for one amenity.
     */
    public function scopeForAmenity($query, $amenityId)
    {
        return $amenityId ? $query->where('amenity_id', $amenityId) : $query;
    }

    /**
     * Only the bookings in one deposit state.
     */
    public function scopeWithDepositStatus($query, ?string $depositStatus)
    {
        return $depositStatus ? $query->where('deposit_status', $depositStatus) : $query;
    }

    /**
     * Only the reservations of one homeowner account.
     */
    public function scopeForUser($query, $userId)
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    /**
     * The reservations that intersect a window — what an availability check
     * and the calendar month both ask for.
     */
    public function scopeOverlapping($query, $from, $to)
    {
        return $query->where('starts_at', '<', $to)->where('ends_at', '>', $from);
    }

    /**
     * The reservations that stand in the way of another one.
     *
     * An approved or completed booking holds the window it was filed for;
     * asking for the same hours is refused while one of those stands. A pending
     * request does not hold anything — several homeowners may ask for the same
     * hour and the office decides which one it takes — and a cancelled booking
     * has released its window.
     */
    public function scopeCommitted($query)
    {
        return $query->whereIn('status', BookingStatus::committedValues());
    }

    // =====================================================================
    // References
    // =====================================================================

    /**
     * The prefix this record's slip references carry, read by
     * \App\Models\Concerns\HasSlipReference::nextReference().
     */
    protected static function referencePrefix(): string
    {
        return 'BKG';
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The BookingStatus enum instance for this booking.
     */
    public function getStatusEnumAttribute(): ?BookingStatus
    {
        return $this->status ? BookingStatus::tryFrom($this->status) : null;
    }

    /**
     * The DepositStatus enum instance for this booking.
     */
    public function getDepositStatusEnumAttribute(): ?DepositStatus
    {
        return $this->deposit_status ? DepositStatus::tryFrom($this->deposit_status) : null;
    }

    /**
     * When the reservation is, as the table writes it: "Jun 14 · 2–6 PM".
     */
    public function getDateTimeDisplayAttribute(): string
    {
        if (! $this->starts_at) {
            return '—';
        }

        return $this->starts_at->format('M j') . ' · ' . TimeHelper::window($this->starts_at, $this->ends_at);
    }

    /**
     * The booked hours alone — "2–6 PM" — for the calendar's day cells.
     */
    public function getTimeRangeDisplayAttribute(): string
    {
        return TimeHelper::window($this->starts_at, $this->ends_at);
    }

    /**
     * The amount as a record writes it: "₱1,500.00", or "Free" when the
     * facility is not paid for at all.
     */
    public function getAmountDisplayAttribute(): string
    {
        return $this->is_free ? 'Free' : MoneyHelper::format($this->rate_amount);
    }

    /**
     * What the reservation costs before the deposit it also asks for — the rate
     * kept on the reservation's own bill. Until the bill is raised (a request
     * still awaiting approval) it previews the amenity's current rate, so the
     * office can see what approving it would charge.
     */
    public function getRateAmountAttribute(): float
    {
        if ($this->bill) {
            return round((float) $this->bill->items->sum('amount') - (float) $this->deposit_amount, 2);
        }

        return (float) ($this->amenity ? $this->amenity->rateFor($this->starts_at, $this->ends_at) : 0);
    }

    /**
     * What this reservation costs at the facility's current rate.
     */
    public function getCurrentRateAmountAttribute(): float
    {
        return $this->amenity
            ? round((float) $this->amenity->rateFor($this->starts_at, $this->ends_at), 2)
            : 0.0;
    }

    /**
     * The lines an invoice for this reservation carries.
     *
     * @return array<int, array<string, mixed>>
     */
    public function invoiceLines(): array
    {
        $lines = [];

        if ($this->current_rate_amount > 0) {
            $lines[] = [
                'name'        => 'Amenity booking — ' . $this->amenity->name,
                'description' => $this->date_time_display . ' · ' . $this->booker_name,
                'quantity'    => 1,
                'unit_price'  => $this->current_rate_amount,
                'amount'      => $this->current_rate_amount,
            ];
        }

        if ((float) $this->deposit_amount > 0) {
            $lines[] = [
                'name'        => 'Deposit — ' . $this->amenity->name,
                'description' => $this->depositInvoiceDescription(),
                'quantity'    => 1,
                'unit_price'  => (float) $this->deposit_amount,
                'amount'      => (float) $this->deposit_amount,
            ];
        }

        return $lines;
    }

    /**
     * The deposit as a record writes it: "₱1,000.00", or a plain statement
     * that the facility asks for none — a deposit column reads as a sentence
     * about the reservation rather than as a bare dash where the office has
     * nothing to hold.
     */
    public function getDepositDisplayAttribute(): string
    {
        return $this->requires_deposit
            ? MoneyHelper::format($this->deposit_amount)
            : 'No deposit required';
    }

    /**
     * Where the deposit stands, as a list writes it on the line under the
     * amount — the status while one is held, and nothing at all when the
     * facility asks for none, so the row never repeats the line above it.
     */
    public function getDepositStatusDisplayAttribute(): ?string
    {
        return $this->requires_deposit ? $this->deposit_status_enum?->label() : null;
    }

    private function depositInvoiceDescription(): string
    {
        return match ($this->deposit_status) {
            DepositStatus::REFUNDED  => 'Refunded after inspection',
            DepositStatus::FORFEITED => 'Forfeited after inspection',
            default                  => 'Refundable after inspection',
        };
    }

    /**
     * The bill's reference, or a dash while none has been raised.
     */
    public function getBillReferenceDisplayAttribute(): string
    {
        return $this->bill ? $this->bill->reference : '—';
    }

    /**
     * When the reservation was paid for: the moment of its bill's latest
     * receipt. Null while nothing has been received.
     */
    public function getPaidAtAttribute()
    {
        return $this->bill ? $this->bill->payments->max('paid_at') : null;
    }

    /**
     * Who the reservation is for: the resident it was filed for, or the guest
     * the office booked it for.
     */
    public function getBookerNameAttribute(): string
    {
        return $this->user?->name ?: ($this->guest_name ?: '—');
    }

    /**
     * Whether this reserve required a deposit when it was filed.
     */
    public function getRequiresDepositAttribute(): bool
    {
        return (float) $this->deposit_amount > 0;
    }

    /**
     * Whether a deposit is still on file for this reservation — one the office
     * has taken and not yet returned or kept.
     */
    public function getDepositIsOpenAttribute(): bool
    {
        return $this->requires_deposit && ! $this->deposit_status_enum?->isSettled();
    }

    /**
     * Whether nothing is charged for this reservation.
     *
     * Not every facility is paid for, so a reservation that costs nothing
     * never goes through the payment step: it is closed as used straight from
     * being approved.
     */
    public function getIsFreeAttribute(): bool
    {
        return $this->rate_amount <= 0;
    }

    /**
     * Whether the office can still approve this reservation.
     */
    public function getCanBeApprovedAttribute(): bool
    {
        return (bool) $this->status_enum?->canBeApproved();
    }

    /**
     * Whether the office approved this reservation — the one state in which its
     * transaction slip is available.
     */
    public function getIsApprovedAttribute(): bool
    {
        return (bool) $this->status_enum?->isApproved();
    }

    /**
     * Whether the office can still raise this reservation's invoice: it is
     * approved, no bill has been raised yet, and there is something to charge
     * (the rate, the deposit, or both).
     */
    public function getCanBeInvoicedAttribute(): bool
    {
        if ($this->bill_id !== null || ! $this->status_enum?->canBePaid()) {
            return false;
        }

        return $this->rate_amount > 0 || $this->deposit_amount > 0;
    }

    /**
     * Whether a payment can still be recorded for this reservation: it is
     * approved, its invoice has been raised, and that bill still carries a
     * balance.
     */
    public function getCanBePaidAttribute(): bool
    {
        if (! $this->status_enum || ! $this->status_enum->canBePaid()) {
            return false;
        }

        return $this->bill !== null && (bool) $this->bill->status_enum?->isCollectible();
    }

    /**
     * Whether the reservation has a bill still waiting to be settled — what the
     * portal shows on the homeowner's own card.
     */
    public function getHasBillWaitingAttribute(): bool
    {
        return (bool) $this->bill?->status_enum?->isCollectible();
    }

    /**
     * Whether the reservation can be closed as used yet: it is approved and
     * nothing is owed on it — a free facility never goes through the payment
     * step, and a charged one waits until its bill is settled.
     */
    public function getCanBeCompletedAttribute(): bool
    {
        $status = $this->status_enum;

        if (! $status || ! $status->canBeCompleted()) {
            return false;
        }

        return $this->is_free || ($this->bill !== null && (bool) $this->bill->status_enum?->isSettled());
    }

    /**
     * Whether the office can still cancel this reservation.
     */
    public function getCanBeCancelledAttribute(): bool
    {
        return (bool) $this->status_enum?->canBeCancelled();
    }

    /**
     * Whether the office still has something to do with this reservation: a
     * move left in its life, or a deposit to settle. A completed reservation
     * whose deposit has been settled is finished.
     *
     * Which moves a status still allows is BookingStatus's call — this only
     * adds the deposit to the question.
     */
    public function getCanStillBeMovedAttribute(): bool
    {
        return ($this->status_enum && ! $this->status_enum->isFinal()) || $this->deposit_is_open;
    }

    /**
     * The unit the reservation belongs to: the unit the booker lives in, when
     * the booking was filed for a resident (see \App\Models\Resident::unit()).
     */
    public function getPropertyLabelAttribute(): ?string
    {
        return $this->user?->household?->property?->block_lot_label;
    }

    /**
     * The unit as the table's own column writes it.
     *
     * Not every reservation names one — the office books a facility for a guest
     * who lives nowhere on file, and a profiled resident may not be linked to a
     * household yet — so the column says which of the two it is instead of
     * leaving a bare dash where a reader expects a place.
     */
    public function getPropertyDisplayAttribute(): string
    {
        if ($this->property_label) {
            return $this->property_label;
        }

        return $this->guest_name ? 'Guest booking' : 'No unit on file';
    }

    /**
     * The steps of the reservation, in the order they happen — what the view
     * modal's timeline walks through.
     *
     * Each step arrives ready to render: the moment it was reached (`when`, or
     * "Not reached" while it has not been), anything worth noting beside it,
     * and how the step reads. Which steps exist depends on how the reservation
     * ended: a cancelled booking shows the cancellation instead of the payment
     * and completion that never came.
     *
     * @return array<int, array{label: string, when: string, note: string|null, icon: string}>
     */
    public function timeline(): array
    {
        $steps = [
            ['label' => 'Submitted', 'at' => $this->created_at, 'note' => null],
            ['label' => 'Approved', 'at' => $this->approved_at, 'note' => null],
        ];

        if ($this->cancelled_at) {
            $steps[] = ['label' => 'Cancelled', 'at' => $this->cancelled_at, 'note' => $this->remarks];
        } else {
            // A free reservation has no payment step to show.
            if (! $this->is_free) {
                $steps[] = ['label' => 'Payment recorded', 'at' => $this->paid_at, 'note' => $this->bill_reference_display];
            }

            $steps[] = [
                'label' => 'Deposit ' . strtolower($this->deposit_status_enum ? $this->deposit_status_enum->label() : 'settled'),
                'at'    => $this->deposit_settled_at,
                'note'  => $this->deposit_display,
            ];
            $steps[] = ['label' => 'Completed', 'at' => $this->completed_at, 'note' => null];
        }

        return array_map(function (array $step) {
            return [
                'label' => $step['label'],
                'when'  => $step['at'] ? $step['at']->format('M j, Y g:i A') : 'Not reached',
                'note'  => $step['note'],
                'icon'  => $step['at']
                    ? 'fas fa-fw fa-check-circle text-success'
                    : 'far fa-fw fa-circle text-muted',
            ];
        }, $steps);
    }
}
