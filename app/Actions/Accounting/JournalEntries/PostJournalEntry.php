<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Services\ActivityLogService;

/**
 * Write a manual journal entry — the treasurer's own posting.
 *
 * The form gives the entry any number of lines, each naming an account, whether
 * the amount goes to it or comes from it, and the amount. JournalEntry::post()
 * balances and numbers the finished posting.
 */
class PostJournalEntry
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): JournalEntry
    {
        $entry = JournalEntry::post([
            'entry_date'     => $data['entry_date'],
            'classification' => JournalClassification::from($data['classification']),
            'source_type'    => JournalSource::MANUAL,
            'source_id'      => null,
            'reference_no'   => filled($data['reference_no'] ?? null) ? trim((string) $data['reference_no']) : null,
            'memo'           => trim((string) $data['memo']),
            'lines'          => $this->lines($data['lines']),
        ], $by->id);

        $this->activity->log(
            $by,
            'created',
            'Journal Entries',
            "Posted journal entry {$entry->entry_no}",
            ['journal_entry_id' => $entry->id]
        );

        return $entry;
    }

    // ===== Helpers =====

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    private function lines(array $lines): array
    {
        return array_map(function (array $line) {
            return [
                'account_id' => (int) $line['account_id'],
                'side'       => $line['side'] === JournalLine::SIDE_CREDIT
                    ? JournalLine::SIDE_CREDIT
                    : JournalLine::SIDE_DEBIT,
                'amount'     => (string) $line['amount'],
            ];
        }, $lines);
    }
}
