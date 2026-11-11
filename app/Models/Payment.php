<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Bill\Bill;
use App\Models\JournalEntry\JournalEntry;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Money received, recorded against the bill it settles.
 *
 * One ledger serves every kind of bill: a payment names the one bill it settled
 * (`bill_id`) — a one-off invoice, a month's dues or an amenity reservation are
 * all rows in the same table now — and a payment made by drawing on credit
 * names the advance deposit it came from instead of a payment method. Nothing
 * about what is owed is stored here — a bill recomputes itself from its
 * payments (see \App\Models\Concerns\RecalculatesBill) — so the ledger is the
 * only record of what was actually collected.
 *
 * A receipt stands in one of two states (see \App\Enums\PaymentStatus): the
 * office takes money over the counter and records it at once, while a payment
 * filed from the portal waits as `pending` — it settles nothing until an
 * officer approves it, and only then does the bill recompute from it.
 */
class Payment extends Model
{
    /** @var string Public-disk directory proofs of payment live in */
    const PROOF_DIRECTORY = 'photos/payment-proofs';

    protected $fillable = [
        'bill_id',
        'advance_deposit_id',
        'user_id',
        'payment_method_id',
        'amount',
        'reference_no',
        'receipt_no',
        'proof_path',
        'paid_at',
        'notes',
        'received_by_admin_id',
        'journal_entry_id',
        'status',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The bill this payment settles.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * The advance deposit the money came from, when it came from credit.
     */
    public function advanceDeposit(): BelongsTo
    {
        return $this->belongsTo(AdvanceDeposit::class);
    }

    /**
     * The homeowner account the payment came from, when one was named.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * How the payment was made.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * The officer who received the payment.
     */
    public function receivedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'received_by_admin_id');
    }

    /**
     * The journal entry this receipt posted.
     *
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the OR number, the bill's reference and the unit.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('reference_no', 'like', $like)
                ->orWhere('receipt_no', 'like', $like)
                ->orWhereHas('bill', function ($bill) use ($like) {
                    $bill->where('reference', 'like', $like)
                        ->orWhere('billed_to_name', 'like', $like);
                })
                ->orWhereHas('user.household.property', function ($property) use ($like) {
                    $property->where('block', 'like', $like)
                        ->orWhere('lot', 'like', $like);
                });
        });
    }

    /**
     * The payments of one account — what the portal's Payments page lists.
     */
    public function scopeForUser($query, $userId)
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    /**
     * Only the payments made through one method.
     */
    public function scopeWithMethod($query, $methodId)
    {
        return $methodId ? $query->where('payment_method_id', $methodId) : $query;
    }

    /**
     * The receipts the office has not taken onto the books yet — money a
     * homeowner says they have handed over, which settles nothing until an
     * officer approves it (see \App\Enums\PaymentStatus::PENDING).
     */
    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    /**
     * Only the receipts the office has taken onto the books — the ones that
     * count against a bill and carry an official receipt number.
     */
    public function scopeRecorded($query)
    {
        return $query->where('status', PaymentStatus::RECORDED);
    }

    /**
     * The payments received in a month, "Y-m".
     */
    public function scopeForMonth($query, ?string $month)
    {
        if (! $month) {
            return $query;
        }

        return $query->whereYear('paid_at', substr($month, 0, 4))
            ->whereMonth('paid_at', substr($month, 5, 2));
    }

    /**
     * Newest first — the ordering the payments ledger opens on.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('paid_at')->orderByDesc('id');
    }

    /**
     * The receipts waiting for approval against one bill.
     */
    public function scopeWaitingForBill($query, $billId)
    {
        return $query->pending()->where('bill_id', $billId)->ordered();
    }

    // =====================================================================
    // References
    // =====================================================================

    /**
     * The next official receipt number, continuing the association's own
     * sequence: OR-000001, OR-000002, …
     *
     * The last number is read with a row lock, so two officers taking money at
     * the same moment cannot be handed the same number; the column's unique
     * index is the backstop when a caller has no transaction open.
     */
    public static function nextReceiptNo(): string
    {
        $prefix = 'OR-';

        $last = static::query()
            ->whereNotNull('receipt_no')
            ->orderByDesc('receipt_no')
            ->lockForUpdate()
            ->value('receipt_no');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The PaymentStatus enum instance for this receipt.
     */
    public function getStatusEnumAttribute(): ?PaymentStatus
    {
        return $this->status ? PaymentStatus::tryFrom($this->status) : null;
    }

    /**
     * AdminLTE badge theme class for this receipt's status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status_enum ? $this->status_enum->badgeTheme() : 'secondary';
    }

    /**
     * The label of this receipt's status, or the raw value when it is unknown.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status_enum
            ? $this->status_enum->label()
            : (string) $this->status;
    }

    /**
     * Whether the office has taken this receipt onto the books yet.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Whether this receipt may be printed.
     */
    public function getIsRecordedAttribute(): bool
    {
        return $this->status === PaymentStatus::RECORDED;
    }

    /**
     * The reference of the bill this payment settles.
     */
    public function getBillReferenceAttribute(): string
    {
        return $this->bill->reference ?? '—';
    }

    /**
     * Which kind of bill this payment settles: "Bill", "Monthly Due", …
     */
    public function getBillTypeLabelAttribute(): string
    {
        return $this->bill ? $this->bill->type_label : '—';
    }

    /**
     * The unit the settled bill's account occupies, as a list writes it —
     * "Blk 7 Lot 12", or a dash while the account holds no household.
     *
     * A payment holds no unit of its own: the unit is reached through the bill's
     * account (see \App\Models\Bill::getPropertyAttribute), the same path the
     * rest of the app uses.
     */
    public function getPropertyLabelAttribute(): string
    {
        return $this->bill?->property?->block_lot_label ?? '—';
    }

    /**
     * How the money arrived: the payment method's name, the advance deposit it
     * was drawn from, or — when the officer named neither, which is what a
     * counter receipt usually is — the association's in-person label.
     */
    public function getMethodLabelAttribute(): string
    {
        if ($this->payment_method_id) {
            return $this->paymentMethod?->name ?? '—';
        }

        if ($this->advance_deposit_id) {
            return 'Advance Deposit';
        }

        return PaymentMethod::IN_PERSON_LABEL;
    }

    /**
     * The amount as a record writes it: "₱350.00".
     */
    public function getAmountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->amount);
    }

    /**
     * URL of the proof of payment on file, or null when none was attached.
     */
    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path ? Storage::disk('public')->url($this->proof_path) : null;
    }

    /**
     * When the payment was received, as a list writes it: "Jun 8, 2026 2:30 PM".
     */
    public function getPaidAtDisplayAttribute(): string
    {
        return $this->paid_at ? $this->paid_at->format('M j, Y g:i A') : '—';
    }
}
