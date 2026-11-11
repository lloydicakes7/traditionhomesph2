<?php

namespace App\Models;

use App\Enums\AdvanceDepositStatus;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * Credit held for a homeowner account — dues paid before the homeowner was billed.
 *
 * Money comes in once (`amount`) and is drawn down as bills are settled from it
 * (`balance`). The balance is the only thing that moves after the deposit is
 * recorded, and it is what the status follows: a deposit is available while it
 * holds credit and used when it does not.
 */
class AdvanceDeposit extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'balance',
        'status',
        'deposited_at',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'balance'      => 'decimal:2',
        'deposited_at' => 'date',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The homeowner account the credit is held for.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The payments drawn from this credit.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the unit and who the credit belongs to.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->orWhereHas('user.household.property', function ($property) use ($like) {
                $property->where('block', 'like', $like)
                    ->orWhere('lot', 'like', $like);
            })->orWhereHas('user.resident', function ($resident) use ($like) {
                $resident->where('first_name', 'like', $like)
                    ->orWhere('surname', 'like', $like);
            });
        });
    }

    /**
     * Only the deposits in one status.
     */
    public function scopeWithStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Only the deposits that still hold credit.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            AdvanceDepositStatus::AVAILABLE,
            AdvanceDepositStatus::PARTIALLY_USED,
        ]);
    }

    /**
     * Only the deposits held for one homeowner account.
     */
    public function scopeForUser($query, $userId)
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    /**
     * Newest first — the ordering the deposits list opens on.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('deposited_at')->orderByDesc('id');
    }

    /**
     * One account's credit, oldest first — the order it is drawn in.
     */
    public function scopeOldestOpenFor($query, $userId)
    {
        return $query->open()->forUser($userId)->orderBy('deposited_at')->orderBy('id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The AdvanceDepositStatus enum instance for this deposit.
     */
    public function getStatusEnumAttribute(): ?AdvanceDepositStatus
    {
        return $this->status ? AdvanceDepositStatus::tryFrom($this->status) : null;
    }

    /**
     * The unit the holder occupies, reached through their household.
     */
    public function getPropertyLabelAttribute(): string
    {
        return $this->user?->household?->property?->block_lot_label ?? '—';
    }

    /**
     * Who the credit belongs to.
     */
    public function getHolderNameAttribute(): string
    {
        return $this->user?->name ?: '—';
    }

    /**
     * The amount received as a record writes it: "₱15,000.00".
     */
    public function getAmountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->amount);
    }

    /**
     * The credit still available as a record writes it: "₱15,000.00".
     */
    public function getBalanceDisplayAttribute(): string
    {
        return MoneyHelper::format($this->balance);
    }

    /**
     * The date the credit was received, as a list writes it: "Jun 8, 2026".
     */
    public function getDepositedAtDisplayAttribute(): string
    {
        return $this->deposited_at ? $this->deposited_at->format('M j, Y') : '—';
    }

    /**
     * Whether the credit still holds something to apply.
     */
    public function getIsOpenAttribute(): bool
    {
        return (float) $this->balance > 0 && (bool) $this->status_enum?->isOpen();
    }

    // =====================================================================
    // Credit
    // =====================================================================

    /**
     * Draw an amount off the credit and move the status to follow it.
     *
     * The balance is the only thing that moves once a deposit is recorded, so
     * how it moves is stated once, here, rather than by each caller that
     * settles a bill from it.
     */
    public function applyCredit(float $amount): void
    {
        $this->balance = round(max(0, (float) $this->balance - $amount), 2);
        $this->status = $this->balance <= 0
            ? AdvanceDepositStatus::USED
            : AdvanceDepositStatus::PARTIALLY_USED;

        $this->save();
    }

    /**
     * Put an amount back on the credit — what undoing a payment drawn from
     * this deposit does.
     */
    public function restoreCredit(float $amount): void
    {
        $this->balance = round((float) $this->balance + $amount, 2);
        $this->status = $this->balance >= (float) $this->amount
            ? AdvanceDepositStatus::AVAILABLE
            : AdvanceDepositStatus::PARTIALLY_USED;

        $this->save();
    }
}
