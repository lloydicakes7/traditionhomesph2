<?php

namespace App\Models\JournalEntry;

use App\Casts\EnumCast;
use App\Enums\JournalClassification;
use App\Enums\JournalEntryStatus;
use App\Enums\JournalSource;
use App\Models\Account\Account;
use App\Models\Account\AccountingPeriod;
use App\Models\Account\AccountTransfer;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * One posting in the association's books — a dated entry of balanced lines.
 *
 * An entry is written by `post()`, which is the only writer: accounting posts
 * each money movement (a payment received, an expense paid, a transfer made)
 * with equal debits and credits, so the books are always live and always
 * balanced. A bill raised is not posted — income is recognised when the receipt
 * that collects it is posted. The record the entry came from is named by
 * `source_type` and `source_id`, giving every posting a trail back to the paper
 * behind it.
 *
 * An entry's lines post to a chart account (\App\Models\Account\Account), except
 * a line recording money from outside the association, which names the external
 * counterparty without an account. The account's *name* is snapshotted onto the
 * line as well, so renaming or retiring an account never rewrites an entry
 * already written.
 *
 * `revise()` is the only writer when an entry is changed. It replaces the lines
 * as a set, so an edit can never leave half of the old posting behind. A manual
 * entry may be removed; an entry a module posted is removed with its source or
 * voided through `voidFor()`, so the module and the books cannot disagree.
 */
class JournalEntry extends Model
{
    const EXTERNAL_ACCOUNT_NAME = 'External funds';

    protected $fillable = [
        'entry_no',
        'entry_date',
        'classification',
        'status',
        'reference_no',
        'memo',
        'source_type',
        'source_id',
        'posted_by_admin_id',
    ];

    protected $casts = [
        'entry_date'     => 'date',
        'classification' => EnumCast::class . ':' . JournalClassification::class,
        'status'         => EnumCast::class . ':' . JournalEntryStatus::class,
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The lines this entry is made of — its debits and credits.
     */
    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * The officer who posted the entry.
     */
    public function postedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'posted_by_admin_id');
    }

    // =====================================================================
    // Posting
    // =====================================================================

    /**
     * Write one double-entry posting.
     *
     * Nothing else creates a `journal_entries` row, so this is where the books'
     * rules live: at least two lines, debits equal to credits, no posting into a
     * closed month, and an entry number that continues the year's own sequence.
     * The account's name is snapshotted onto each line, so renaming or retiring
     * an account never rewrites an entry already written.
     *
     * @param  array<string, mixed>  $data  `entry_date`, `classification`,
     *                                      `memo`, `source_type`, `source_id`, `reference_no`
     *                                      and `lines` — each an `account_id` (or an
     *                                      `account_name` for money from outside),
     *                                      a `side` (debit|credit), an `amount` and an
     *                                      optional `memo`
     * @param  int  $adminId  The officer whose action posted the entry
     *
     * @throws \Illuminate\Validation\ValidationException when the entry is unbalanced or the month is closed
     */
    public static function post(array $data, int $adminId): JournalEntry
    {
        $lines = static::resolveLines($data['lines'] ?? []);

        static::assertBalanced($lines);

        $entryDate = $data['entry_date'] ?? now();

        static::assertPeriodOpen($entryDate);

        return DB::transaction(function () use ($data, $lines, $adminId, $entryDate) {
            $entry = static::create([
                'entry_no'           => static::nextEntryNo(),
                'entry_date'         => $entryDate,
                'classification'     => $data['classification'] ?? JournalClassification::STANDARD(),
                'status'             => JournalEntryStatus::POSTED,
                'reference_no'       => ($data['reference_no'] ?? null) ?: null,
                'memo'               => ($data['memo'] ?? null) ?: null,
                'source_type'        => $data['source_type'] ?? null,
                'source_id'          => $data['source_id'] ?? null,
                'posted_by_admin_id' => $adminId,
            ]);

            $entry->lines()->createMany($lines);

            return $entry;
        });
    }

    /**
     * Change an entry already posted.
     *
     * The entry number, source and original poster stay as they were. When
     * `lines` is given the old lines are replaced as a set; when it is not, the
     * posting keeps its lines and only the description fields change. The same
     * balance and closed-month rules apply as when it was first written.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function revise(array $data): JournalEntry
    {
        if ($this->status && $this->status->is(JournalEntryStatus::VOID())) {
            throw ValidationException::withMessages([
                'entry' => 'A voided entry cannot be changed.',
            ]);
        }

        if ($this->source_enum && $this->source_enum->isAutomatic() && array_key_exists('lines', $data)) {
            throw ValidationException::withMessages([
                'lines' => 'This entry came from a ' . strtolower($this->source_label)
                    . '. Its amount and accounts stay as that record posted them.',
            ]);
        }

        $entryDate = $data['entry_date'] ?? $this->entry_date;

        static::assertPeriodOpen($this->entry_date);
        static::assertPeriodOpen($entryDate);

        $replacingLines = array_key_exists('lines', $data);
        $lines = $replacingLines ? static::resolveLines($data['lines'] ?? []) : null;

        if ($replacingLines) {
            static::assertBalanced($lines);
        }

        DB::transaction(function () use ($data, $entryDate, $lines, $replacingLines) {
            $this->update([
                'entry_date'     => $entryDate,
                'classification' => isset($data['classification'])
                    ? ($data['classification'] instanceof JournalClassification
                        ? $data['classification']
                        : JournalClassification::from($data['classification']))
                    : $this->classification,
                'reference_no'   => array_key_exists('reference_no', $data)
                    ? (($data['reference_no'] ?? null) ?: null)
                    : $this->reference_no,
                'memo'           => $data['memo'] ?? $this->memo,
            ]);

            if ($replacingLines) {
                $this->lines()->delete();
                $this->lines()->createMany($lines);
            }
        });

        $this->unsetRelation('lines');

        return $this->load(['lines.account', 'postedByAdmin']);
    }

    /**
     * Remove a manual entry and its lines.
     *
     * Entries a module posted are not removed here: their source record still
     * points at them, so they are removed with that record or marked void.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function remove(): void
    {
        if ($this->status && $this->status->is(JournalEntryStatus::VOID())) {
            throw ValidationException::withMessages([
                'entry' => 'A voided entry cannot be removed.',
            ]);
        }

        if ($this->source_enum && $this->source_enum->isAutomatic()) {
            throw ValidationException::withMessages([
                'entry' => 'This entry came from a ' . strtolower($this->source_label)
                    . '. Remove it from that record instead — the books must keep the trail.',
            ]);
        }

        static::assertPeriodOpen($this->entry_date);

        DB::transaction(function () {
            $this->lines()->delete();
            $this->delete();
        });
    }

    /**
     * Void the entry a record posted, when the record no longer stands on its
     * own — a bill withdrawn, a receipt removed.
     *
     * The entry itself is kept and marked void rather than deleted, because the
     * record that wrote it still points at the paper trail it left behind.
     */
    public static function voidFor($record): void
    {
        if (! $record->journal_entry_id) {
            return;
        }

        static::whereKey($record->journal_entry_id)
            ->update(['status' => JournalEntryStatus::VOID]);

        $record->update(['journal_entry_id' => null]);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Only the entries that count towards the books' balances.
     */
    public function scopePosted($query)
    {
        return $query->where('status', JournalEntryStatus::POSTED);
    }

    /**
     * The entries dated inside a period, both ends included.
     */
    public function scopeBetween($query, $from, $to)
    {
        return $query->whereDate('entry_date', '>=', $from)
            ->whereDate('entry_date', '<=', $to);
    }

    /**
     * Free-text search across the entry number, the reference and the memo.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('entry_no', 'like', $like)
                ->orWhere('reference_no', 'like', $like)
                ->orWhere('memo', 'like', $like);
        });
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The date the entry was posted, as a record writes it: "Jun 8, 2026".
     */
    public function getEntryDateDisplayAttribute(): string
    {
        return $this->entry_date ? $this->entry_date->format('M j, Y') : '—';
    }

    /**
     * The source module's label, safe when the stored value is unknown.
     */
    public function getSourceLabelAttribute(): string
    {
        $source = $this->source_enum;

        return $source ? $source->label() : 'Manual';
    }

    /**
     * The source module's badge theme, safe when the stored value is unknown.
     */
    public function getSourceBadgeAttribute(): string
    {
        $source = $this->source_enum;

        return $source ? $source->badgeTheme() : 'secondary';
    }

    /**
     * The JournalSource enum instance behind `source_type`, when it is known.
     */
    public function getSourceEnumAttribute(): ?JournalSource
    {
        return $this->source_type ? JournalSource::tryFrom($this->source_type) : null;
    }

    /**
     * The account on the receiving side of the posting, in the officer's words.
     * A posting split across several accounts says so rather than naming one.
     */
    public function getGoesToAttribute(): string
    {
        return $this->accountNamesOn(JournalLine::SIDE_DEBIT);
    }

    /**
     * The account on the paying side of the posting, in the officer's words.
     */
    public function getComesFromAttribute(): string
    {
        return $this->accountNamesOn(JournalLine::SIDE_CREDIT);
    }

    /**
     * The description the entry came with, where its source record carries one.
     * A manual entry falls back to its own memo.
     */
    public function getDescriptionTextAttribute(): string
    {
        $description = null;

        if ($this->source_type === JournalSource::PAYMENT) {
            $payment = Payment::find($this->source_id);
            $description = $payment ? $payment->notes : null;
        } elseif ($this->source_type === JournalSource::EXPENSE) {
            $expense = Expense::find($this->source_id);
            $description = $expense ? $expense->description : null;
        } elseif ($this->source_type === JournalSource::TRANSFER) {
            $transfer = AccountTransfer::find($this->source_id);
            $description = $transfer ? $transfer->memo : null;
        }

        return filled($description) ? (string) $description : ($this->memo ?: '—');
    }

    /**
     * The status's label, safe when the stored value is unknown.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? $this->status->label() : '—';
    }

    /**
     * The status's badge theme, safe when the stored value is unknown.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status ? $this->status->badgeTheme() : 'secondary';
    }

    /**
     * The classification's label, safe when the stored value is unknown.
     */
    public function getClassificationLabelAttribute(): string
    {
        return $this->classification ? $this->classification->label() : '—';
    }

    /**
     * What the entry comes to on its receiving side.
     */
    public function totalDebits(): string
    {
        return $this->totalOn(JournalLine::SIDE_DEBIT);
    }

    /**
     * What the entry comes to on its paying side.
     */
    public function totalCredits(): string
    {
        return $this->totalOn(JournalLine::SIDE_CREDIT);
    }

    /**
     * The amount of the posting, as a decimal string.
     */
    public function getTotalAmountAttribute(): string
    {
        return $this->totalDebits();
    }

    /**
     * Whether the entry's two sides come to the same amount — the one rule that
     * makes it a posting rather than a note.
     */
    public function isBalanced(): bool
    {
        $debits = $this->totalDebits();
        $credits = $this->totalCredits();

        return bccomp($debits, $credits, 2) === 0 && bccomp($debits, '0', 2) > 0;
    }

    /**
     * Whether the entry still counts towards the books.
     */
    public function isPosted(): bool
    {
        return $this->status ? $this->status->affectsBalances() : false;
    }

    // =====================================================================
    // Posting helpers
    // =====================================================================

    /**
     * The names of the accounts on one side, or "Several" when the posting
     * touches more than one account there.
     */
    private function accountNamesOn(string $side): string
    {
        $lines = $this->relationLoaded('lines')
            ? $this->lines
            : $this->lines()->with('account')->get();

        $names = $lines
            ->where('side', $side)
            ->map(function (JournalLine $line) {
                return $line->account ? $line->account->label : $line->account_name;
            })
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return '—';
        }

        return $names->count() === 1 ? (string) $names->first() : 'Several';
    }

    /**
     * Add up one side of the entry without ever turning money into a float.
     */
    private function totalOn(string $side): string
    {
        $lines = $this->relationLoaded('lines') ? $this->lines : $this->lines()->get();

        $total = '0.00';

        foreach ($lines as $line) {
            if ($line->side === $side) {
                $total = bcadd($total, (string) $line->amount, 2);
            }
        }

        return $total;
    }

    /**
     * Normalize the caller's lines: resolve their accounts or external
     * counterparties, drop the empty ones, round the amounts.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    private static function resolveLines(array $lines): array
    {
        $resolved = [];

        foreach ($lines as $line) {
            $raw = $line['amount'] ?? 0;
            $amount = bcadd($raw === '' ? '0' : (string) $raw, '0', 2);

            if (bccomp($amount, '0', 2) <= 0) {
                continue;
            }

            $account = static::resolveAccount($line);
            $accountName = $account
                ? $account->name
                : trim((string) ($line['account_name'] ?? ''));

            if ($accountName === '') {
                continue;
            }

            $resolved[] = [
                'account_id'   => $account ? $account->id : null,
                'account_name' => $accountName,
                'side'         => ($line['side'] ?? '') === JournalLine::SIDE_CREDIT
                    ? JournalLine::SIDE_CREDIT
                    : JournalLine::SIDE_DEBIT,
                'amount'       => $amount,
                'memo'         => filled($line['memo'] ?? null) ? $line['memo'] : null,
            ];
        }

        return $resolved;
    }

    /**
     * The account a caller's line names.
     */
    private static function resolveAccount(array $line): ?Account
    {
        if (! empty($line['account_id'])) {
            return Account::find($line['account_id']);
        }

        return null;
    }

    /**
     * Refuse to write an entry whose debits and credits do not agree — the one
     * rule that makes the books a set of books.
     *
     * @param  array<int, array<string, mixed>>  $lines
     */
    private static function assertBalanced(array $lines): void
    {
        if (count($lines) < 2) {
            throw ValidationException::withMessages([
                'lines' => 'A journal entry needs at least two lines — one receiving account and one paying account.',
            ]);
        }

        $debits = '0.00';
        $credits = '0.00';

        foreach ($lines as $line) {
            if ($line['side'] === JournalLine::SIDE_DEBIT) {
                $debits = bcadd($debits, (string) $line['amount'], 2);
            } else {
                $credits = bcadd($credits, (string) $line['amount'], 2);
            }
        }

        if (bccomp($debits, '0', 2) <= 0 || bccomp($debits, $credits, 2) !== 0) {
            throw ValidationException::withMessages([
                'lines' => 'The entry is not balanced: what goes to the accounts must equal what comes from them.',
            ]);
        }
    }

    /**
     * Refuse a date in a closed month.
     */
    private static function assertPeriodOpen($date): void
    {
        if (AccountingPeriod::locksDate($date)) {
            throw ValidationException::withMessages([
                'entry_date' => 'That month is closed — reopen the period before changing the books there.',
            ]);
        }
    }

    /**
     * The next journal entry number, continuing the books' own sequence:
     * JE-2026-000001, JE-2026-000002, …
     */
    private static function nextEntryNo(): string
    {
        $prefix = 'JE-' . now()->year . '-';

        $last = static::query()
            ->where('entry_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('entry_no');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
