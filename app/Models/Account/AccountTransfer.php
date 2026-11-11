<?php

namespace App\Models\Account;

use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Money moved between the association's own accounts.
 *
 * A transfer is neither income nor an expense — it is the same money, one
 * account lighter and another heavier — so it is a record of its own rather
 * than a bill or a payout. What it needs is the two accounts, the amount, the
 * day it happened and the journal entry it posted, so the register can be read
 * back and the posting traced: the entry is written by JournalEntry::post() like
 * every other movement, and this record only says why the money moved.
 *
 * The accounts named are the chart accounts money sits in (`is_cash`), the
 * same accounts an expense is paid from and a receipt lands in
 * against, so a transfer and the cash register cannot disagree about which
 * account holds the money.
 */
class AccountTransfer extends Model
{
    protected $fillable = [
        'reference',
        'transfer_date',
        'from_account_id',
        'to_account_id',
        'amount',
        'memo',
        'reference_no',
        'journal_entry_id',
        'created_by_admin_id',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount'        => 'decimal:2',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The chart account the money left.
     */
    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * The chart account the money arrived in.
     */
    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    /**
     * The entry that moved the money.
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * The officer who recorded the transfer.
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the reference, the memo and the two accounts.
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
                ->orWhere('memo', 'like', $like)
                ->orWhere('reference_no', 'like', $like)
                ->orWhereHas('fromAccount', function ($query) use ($like) {
                    $query->where('name', 'like', $like)->orWhere('code', 'like', $like);
                })
                ->orWhereHas('toAccount', function ($query) use ($like) {
                    $query->where('name', 'like', $like)->orWhere('code', 'like', $like);
                });
        });
    }

    /**
     * The transfers of one month, "Y-m".
     */
    public function scopeOfMonth($query, string $month)
    {
        return $query->whereYear('transfer_date', (int) substr($month, 0, 4))
            ->whereMonth('transfer_date', (int) substr($month, 5, 2));
    }

    /**
     * The register's own order: newest first.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('transfer_date')->orderByDesc('id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The date as a record writes it: "Jun 8, 2026".
     */
    public function getTransferDateDisplayAttribute(): string
    {
        return $this->transfer_date ? $this->transfer_date->format('M j, Y') : '—';
    }

    /**
     * The amount as a record writes it: "₱45,000.00".
     */
    public function getAmountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->amount);
    }

    /**
     * Where the money came from, as a row writes it: "1010 · BPI Checking".
     */
    public function getFromLabelAttribute(): string
    {
        return $this->fromAccount ? $this->fromAccount->label : '—';
    }

    /**
     * Where the money went.
     */
    public function getToLabelAttribute(): string
    {
        return $this->toAccount ? $this->toAccount->label : '—';
    }

    /**
     * Whether the transfer still counts — the entry behind it was not voided.
     */
    public function isPosted(): bool
    {
        return $this->journal_entry_id !== null
            && $this->journalEntry
            && $this->journalEntry->isPosted();
    }
}
