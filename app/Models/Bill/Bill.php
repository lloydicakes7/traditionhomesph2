<?php

namespace App\Models\Bill;

use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Concerns\RecalculatesBill;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One thing a homeowner owes, whatever raised it.
 *
 * A bill replaces the two records the collections ledger used to keep apart —
 * the one-off invoice and the month's dues. Both carried a reference, money
 * columns, a status and a posting, and every page that read one had to remember
 * the other existed. Now there is one record and `type` says where it came from
 * (see \App\Enums\BillType): a bill is a bill, and the screen it appears on is a
 * filter over this table, not a different table.
 *
 * Who owes it is the homeowner account (`user_id`), not the unit. The unit an
 * account occupies is reached through `users -> households -> properties` — the
 * same path the rest of the app uses — so nothing here stores a `property_id`
 * that could drift away from the household on file. `user_id` is nullable
 * because the office may bill an amenity for a non-resident guest; in that case
 * `billed_to_name` carries who the bill was written for.
 *
 * A bill's money follows two facts and nothing else: its lines give the
 * subtotal, its payments give what is paid, and `recalculate()` writes the
 * totals and the status they imply (see \App\Models\Concerns\RecalculatesBill).
 * Nothing stores a running balance the rest of the app has to trust.
 *
 * A month's dues are the only type with a `billing_period`, and a homeowner is
 * billed once per type per period — the table's unique index enforces it and
 * the generator checks it.
 */
class Bill extends Model
{
    use RecalculatesBill;

    protected $fillable = [
        'reference',
        'user_id',
        'billed_to_name',
        'type',
        'billing_period',
        'bill_date',
        'due_date',
        'subtotal',
        'discount',
        'penalty',
        'total',
        'paid_amount',
        'balance',
        'status',
        'notes',
        'created_by_admin_id',
        'reminder_sent_at',
    ];

    protected $casts = [
        'bill_date'        => 'date',
        'due_date'         => 'date',
        'subtotal'         => 'decimal:2',
        'discount'         => 'decimal:2',
        'penalty'          => 'decimal:2',
        'total'            => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'balance'          => 'decimal:2',
        'reminder_sent_at' => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The homeowner account the bill is issued to, when it names one.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The lines this bill is made of.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    /**
     * The payments recorded against this bill.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('paid_at');
    }

    /**
     * Write the payment received against this bill and re-price the bill.
     *
     * Money reaches a bill the same way whichever page takes it — the counter,
     * the portal, an advance deposit, an amenity reservation — so a payment is
     * written and the bill is re-priced from the payments on file in one step.
     * `paid_amount`, `balance` and `status` are never set from a form.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function settle(array $attributes): Payment
    {
        $attributes = array_merge([
            'user_id' => $this->user_id,
            'paid_at' => now(),
            'status'  => PaymentStatus::RECORDED,
        ], $attributes);

        if ($attributes['status'] === PaymentStatus::RECORDED && empty($attributes['receipt_no'])) {
            $attributes['receipt_no'] = Payment::nextReceiptNo();
        }

        $payment = $this->payments()->create($attributes);

        $this->recalculate();

        return $payment;
    }

    /**
     * The officer who raised the bill.
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Only the one-off invoices and the invoices amenity bookings raised.
     */
    public function scopeInvoices($query)
    {
        return $query->whereIn('type', [BillType::INVOICE, BillType::AMENITY_BOOKING]);
    }

    /**
     * Only the recurring monthly dues.
     */
    public function scopeMonthlyDues($query)
    {
        return $query->where('type', BillType::MONTHLY_DUE);
    }

    /**
     * Only the amenity reservations.
     */
    public function scopeAmenityBookings($query)
    {
        return $query->where('type', BillType::AMENITY_BOOKING);
    }

    /**
     * Every charge that is not the recurring monthly dues — a one-off invoice
     * and an amenity reservation both.
     *
     */
    public function scopeCharges($query)
    {
        return $query->where('type', '!=', BillType::MONTHLY_DUE);
    }

    /**
     * Only the bills still carrying something to collect.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            BillStatus::UNPAID,
            BillStatus::PARTIAL,
            BillStatus::OVERDUE,
        ]);
    }

    /**
     * Free-text search across the reference, who it is billed to and the unit.
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
                ->orWhere('billed_to_name', 'like', $like)
                ->orWhereHas('user.resident', function ($resident) use ($like) {
                    $resident->where('first_name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                })
                ->orWhereHas('user.household.property', function ($property) use ($like) {
                    $property->where('block', 'like', $like)
                        ->orWhere('lot', 'like', $like);
                });
        });
    }

    /**
     * Only the bills in one status.
     */
    public function scopeWithStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * The bills of one month, "Y-m".
     *
     */
    public function scopeForMonth($query, ?string $month)
    {
        if (! $month) {
            return $query;
        }

        return $query->where(function ($query) use ($month) {
            $query->where('billing_period', $month)
                ->orWhere(function ($query) use ($month) {
                    $query->whereYear('bill_date', substr($month, 0, 4))
                        ->whereMonth('bill_date', substr($month, 5, 2));
                });
        });
    }

    /**
     * Only the bills of one account.
     */
    public function scopeForUser($query, $userId)
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    /**
     * Newest first — the ordering every bill list opens on.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('bill_date')->orderByDesc('id');
    }

    // =====================================================================
    // References
    // =====================================================================

    /**
     * The next reference for a bill of this type, continuing the association's
     * own sequence.
     *
     * The prefix says where the bill came from — `INV-`, `DUE-`, `AMN-` — and a
     * month's dues add the period they bill, so a month's references read
     * `DUE-202610-0001`. Every module that raises a bill asks for its reference
     * here, so the numbering is written once.
     *
     * @param  string|null  $period  Billing month, "Y-m"; monthly dues only
     */
    public static function nextReference(BillType $type, ?string $period = null): string
    {
        $prefix = $type->referencePrefix();

        if ($type->isMonthlyDue()) {
            $prefix .= str_replace('-', '', $period ?: now()->format('Y-m')) . '-';
        } else {
            $prefix .= now()->year . '-';
        }

        $last = static::query()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The unit the billed account occupies.
     *
     */
    public function getPropertyAttribute(): ?Property
    {
        return $this->user?->household?->property;
    }

    /**
     * The BillType enum instance for this bill.
     */
    public function getTypeEnumAttribute(): ?BillType
    {
        return $this->type ? BillType::tryFrom($this->type) : null;
    }

    /**
     * What raised this bill, as a list writes it: "Bill".
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type_enum ? $this->type_enum->label() : '—';
    }

    /**
     * Who the bill is issued to: the name snapshotted when it was raised, or the
     * account's own name.
     */
    public function getIssuedToNameAttribute(): string
    {
        return $this->billed_to_name ?: ($this->user?->name ?: '—');
    }

    /**
     * The date the bill was raised, as a list writes it: "Jun 8, 2026".
     */
    public function getBillDateDisplayAttribute(): string
    {
        return $this->bill_date ? $this->bill_date->format('M j, Y') : '—';
    }

    /**
     * The billing month as a record writes it: "June 2026".
     */
    public function getBillingPeriodDisplayAttribute(): string
    {
        if (! $this->billing_period) {
            return '—';
        }

        return Carbon::createFromFormat('Y-m', $this->billing_period)->format('F Y');
    }

    /**
     * What the bill's own lines come to, before any discount or penalty.
     */
    protected function billSubtotal(): float
    {
        return (float) $this->items()->sum('amount');
    }
}
