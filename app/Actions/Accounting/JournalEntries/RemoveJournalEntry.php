<?php

namespace App\Actions\Accounting\JournalEntries;

use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

/**
 * Remove a manual journal entry.
 *
 * An entry a payment, expense, transfer or cash funding wrote is not removed
 * from here: that record still points at the posting. It is removed with its
 * source, or marked void when the source is withdrawn.
 */
class RemoveJournalEntry
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(JournalEntry $entry, Admin $by): void
    {
        if ($entry->source_enum && $entry->source_enum->isAutomatic()) {
            throw ValidationException::withMessages([
                'entry' => 'This entry came from a ' . strtolower($entry->source_label)
                    . '. Remove it from that record instead — the books must keep the trail.',
            ]);
        }

        $id = $entry->id;
        $entryNo = $entry->entry_no;

        $entry->remove();

        $this->activity->log(
            $by,
            'deleted',
            'Journal Entries',
            "Removed journal entry {$entryNo}",
            ['journal_entry_id' => $id]
        );
    }
}
