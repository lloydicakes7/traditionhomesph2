<?php

namespace App\Http\Livewire\Admin\Accounting\JournalEntries\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Accounting\JournalEntryListQuery;

/**
 * View Journal Entry — one entry, read as the books write it: what it is, where
 * it came from, and its lines — an account, and the amount that goes to it or
 * comes from it — with the two sides equal, every time.
 *
 * Opened client-side by Bootstrap (`#journalEntryViewModal`) with the entry's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. The dialog is
 * shared with the Transfers and Transactions pages, so a posting
 * can be read wherever it is referenced.
 *
 * The entry's own voucher can be printed from here: the button hands off to a
 * normal route, because Livewire cannot stream a download (see
 * \App\Http\Controllers\Admin\AccountingController@voucher).
 */
class JournalEntryViewModal extends AdminModalComponent
{
    const MODAL_ID = 'journalEntryViewModal';

    /** @var int|null Entry being read */
    public $entryId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadJournalEntryViewModal'  => 'load',
        'closeJournalEntryViewModal' => 'closeModal',
    ];

    /** @var JournalEntryListQuery */
    protected $journalEntries;

    public function boot(JournalEntryListQuery $journalEntries)
    {
        $this->journalEntries = $journalEntries;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', JournalEntry::class);

        $this->entryId = (int) $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->entryId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $entry = $this->loaded && $this->entryId
            ? $this->journalEntries->find($this->entryId)
            : null;

        return view('livewire.admin.accounting.journal-entries.modals.journal-entry-view-modal', [
            'entry' => $entry,
            'lines' => $entry ? $this->journalEntries->linesFor($entry) : collect(),
        ]);
    }
}
