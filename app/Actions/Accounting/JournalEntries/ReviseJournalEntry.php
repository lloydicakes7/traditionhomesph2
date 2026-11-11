<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Enums\JournalClassification;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Services\ActivityLogService;

/**
 * Change a journal entry.
 *
 * A manual entry can have its lines corrected. An entry a module posted keeps
 * the amount and accounts that module recorded; only its memo, classification,
 * reference and date can be clarified here. Either way the model replaces the
 * lines as a set so the posting cannot end up half-edited.
 */
class ReviseJournalEntry
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(JournalEntry $entry, array $data, Admin $by): JournalEntry
    {
        $data = [
            'entry_date'     => $data['entry_date'],
            'classification' => JournalClassification::from($data['classification']),
            'reference_no'   => filled($data['reference_no'] ?? null) ? trim((string) $data['reference_no']) : null,
            'memo'           => trim((string) $data['memo']),
        ] + ($this->isAutomatic($entry) ? [] : [
            'lines' => $this->lines($data['lines']),
        ]);

        $entry = $entry->revise($data);

        $this->activity->log(
            $by,
            'updated',
            'Journal Entries',
            "Revised journal entry {$entry->entry_no}",
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

    /**
     * Whether the posting came from another module, whose amount and accounts
     * are the source record's own.
     */
    private function isAutomatic(JournalEntry $entry): bool
    {
        return $entry->source_enum ? $entry->source_enum->isAutomatic() : false;
    }
}
