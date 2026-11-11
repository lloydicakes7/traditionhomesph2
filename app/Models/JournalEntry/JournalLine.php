<?php

namespace App\Models\JournalEntry;

use App\Models\Account\Account;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of a journal entry — a debit or a credit against a chart account.
 *
 * A line names the account it posts to (\App\Models\Account\Account) and carries a
 * snapshot of that account's name, so an entry already written keeps saying
 * what it said even after the account is renamed or switched off. A line for
 * money from outside the association names the external counterparty instead.
 * An entry is balanced when its debits and credits come to the same amount —
 * JournalEntry::post() sees to that before it writes one.
 */
class JournalLine extends Model
{
    const SIDE_DEBIT  = 'debit';
    const SIDE_CREDIT = 'credit';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'account_name',
        'side',
        'amount',
        'memo',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The entry this line belongs to.
     */
    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * The chart account the line posts to.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Only the lines posted against one account.
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Only the lines on one side of the books.
     */
    public function scopeOnSide($query, string $side)
    {
        return $query->where('side', $side);
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The amount as a record writes it: "₱350.00".
     */
    public function getAmountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->amount);
    }

    /**
     * The debit column as the entry's view writes it — the amount, or nothing
     * at all when this line is on the other side.
     */
    public function getDebitDisplayAttribute(): string
    {
        return $this->side === self::SIDE_DEBIT ? MoneyHelper::format($this->amount) : '—';
    }

    /**
     * The credit column as the entry's view writes it.
     */
    public function getCreditDisplayAttribute(): string
    {
        return $this->side === self::SIDE_CREDIT ? MoneyHelper::format($this->amount) : '—';
    }

    /**
     * Whether this line is a debit.
     */
    public function isDebit(): bool
    {
        return $this->side === self::SIDE_DEBIT;
    }

    /**
     * What this line does to the account it posts to, signed for the ledger: a
     * debit adds, a credit subtracts.
     */
    public function signedAmount(): float
    {
        $amount = (float) $this->amount;

        return $this->isDebit() ? $amount : -$amount;
    }
}
