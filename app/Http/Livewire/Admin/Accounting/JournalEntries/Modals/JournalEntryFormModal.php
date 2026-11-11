<?php

namespace App\Http\Livewire\Admin\Accounting\JournalEntries\Modals;

use App\Actions\Accounting\JournalEntries\PostJournalEntry;
use App\Actions\Accounting\JournalEntries\RemoveJournalEntry;
use App\Actions\Accounting\JournalEntries\ReviseJournalEntry;
use App\Enums\JournalClassification;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\EditsJournalLines;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Accounting\JournalEntryListQuery;
use Illuminate\Validation\ValidationException;

/**
 * New / Edit Journal Entry — the treasurer's own pen.
 *
 * The form is one date, one classification, an optional reference and one memo,
 * then as many account lines as the posting needs. Each line says whether its
 * amount goes to the account or comes from it; the totals are shown as they are
 * typed and the entry is refused unless the two sides agree.
 *
 * An entry a module posted opens in the same window, but its amount and account
 * lines belong to the record that wrote it. They stay read-only; the officer can
 * only clarify the date, classification, reference and memo.
 */
class JournalEntryFormModal extends AdminModalComponent
{
    use EditsJournalLines;

    const MODAL_ID = 'journalEntryFormModal';

    /** @var bool Editing an existing entry when true */
    public $isEditing = false;

    /** @var int|null Entry being changed; null when writing a new entry */
    public $editingEntryId = null;

    /** @var string Entry number shown while editing */
    public $editingEntryLabel = '';

    /** @var string Source shown while editing a module posting */
    public $editingSourceLabel = '';

    /** @var bool Whether the entry came from a module and its lines are locked */
    public $isSystemEntry = false;

    /** @var bool The remove confirmation is showing */
    public $confirmingRemove = false;

    /** @var string */
    public $entry_date = '';

    /** @var string */
    public $classification = JournalClassification::STANDARD;

    /** @var string */
    public $reference_no = '';

    /** @var string */
    public $memo = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openJournalEntryFormModal'  => 'openCreate',
        'loadJournalEntryFormModal'  => 'openEdit',
        'closeJournalEntryFormModal' => 'closeModal',
    ];

    /** @var JournalEntryListQuery */
    protected $journalEntries;

    public function boot(JournalEntryListQuery $journalEntries)
    {
        $this->journalEntries = $journalEntries;
    }

    // =====================================================================
    // Lifecycle
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', JournalEntry::class);

        $this->isEditing = false;
        $this->editingEntryId = null;
        $this->editingEntryLabel = '';
        $this->editingSourceLabel = '';
        $this->isSystemEntry = false;
        $this->resetEntry();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', JournalEntry::class);

        $entry = $this->journalEntries->find($id);

        $this->isEditing = true;
        $this->editingEntryId = $entry->id;
        $this->editingEntryLabel = $entry->entry_no;
        $this->editingSourceLabel = $entry->source_label;
        $this->isSystemEntry = $entry->source_enum ? $entry->source_enum->isAutomatic() : false;
        $this->resetEntry();

        $this->entry_date = $entry->entry_date ? $entry->entry_date->toDateString() : now()->toDateString();
        $this->classification = $entry->classification ? $entry->classification->value : JournalClassification::STANDARD;
        $this->reference_no = (string) $entry->reference_no;
        $this->memo = (string) $entry->memo;
        $this->lines = $entry->lines->map(function ($line) {
            return [
                'account_id'   => (string) $line->account_id,
                'account_name' => (string) $line->account_name,
                'side'         => $line->isDebit() ? 'debit' : 'credit',
                'amount'       => (string) $line->amount,
            ];
        })->all();

        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingEntryId = null;
        $this->editingEntryLabel = '';
        $this->editingSourceLabel = '';
        $this->isSystemEntry = false;
        $this->resetEntry();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetEntry(): void
    {
        $this->entry_date = now()->toDateString();
        $this->classification = JournalClassification::STANDARD;
        $this->reference_no = '';
        $this->memo = '';
        $this->lines = [$this->blankLine('debit'), $this->blankLine('credit')];
        $this->confirmingRemove = false;
    }

    // =====================================================================
    // Actions
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', JournalEntry::class);

        if ($this->isEditing && ! $this->editingEntryId) {
            return;
        }

        if (! $this->isSystemEntry) {
            $this->normalizeLines();
        }

        $this->validate($this->rules(), $this->messages());

        if (! $this->isSystemEntry && ! $this->isBalanced()) {
            throw ValidationException::withMessages([
                'lines' => 'The entry is not balanced — what goes to the accounts must equal what comes from them.',
            ]);
        }

        $data = [
            'entry_date'     => $this->entry_date,
            'classification' => $this->classification,
            'reference_no'   => $this->reference_no,
            'memo'           => $this->memo,
        ];

        if (! $this->isSystemEntry) {
            $data['lines'] = $this->journalLines();
        }

        if ($this->isEditing) {
            $entry = app(ReviseJournalEntry::class)->handle(
                $this->journalEntries->find($this->editingEntryId),
                $data,
                auth('web')->user()
            );

            $message = 'Journal entry ' . $entry->entry_no . ' updated.';
        } else {
            $entry = app(PostJournalEntry::class)->handle($data, auth('web')->user());

            $message = 'Journal entry ' . $entry->entry_no . ' posted.';
        }

        $this->emit('notify', 'success', $message);
        $this->emit('journal-entry-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    public function removeEntry(): void
    {
        if (! $this->loaded || ! $this->isEditing || ! $this->editingEntryId) {
            return;
        }

        $this->authorize('delete', JournalEntry::class);

        $entry = $this->journalEntries->find($this->editingEntryId);
        $entryNo = $entry->entry_no;

        app(RemoveJournalEntry::class)->handle($entry, auth('web')->user());

        $this->emit('notify', 'success', 'Journal entry ' . $entryNo . ' removed.');
        $this->emit('journal-entry-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Rules
    // =====================================================================

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $rules = [
            'entry_date'     => 'required|date',
            'classification' => 'required|in:' . implode(',', array_keys(JournalClassification::options())),
            'reference_no'   => 'nullable|string|max:100',
            'memo'           => 'required|string|max:255',
        ];

        return $this->isSystemEntry ? $rules : $rules + $this->lineRules();
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'entry_date.required'            => 'Give the entry a date.',
            'memo.required'                  => 'Write a memo for this entry.',
            'lines.required'                 => 'Add at least two lines.',
            'lines.min'                      => 'A journal entry needs at least two lines.',
            'lines.*.account_id.required'    => 'Choose an account for every line.',
            'lines.*.amount.required'        => 'Enter an amount for every line.',
            'lines.*.amount.min'             => 'Every line must be greater than zero.',
            'lines.*.side.required'          => 'Choose whether the amount goes to or comes from the account.',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.journal-entries.modals.journal-entry-form-modal', [
            'accountOptions'       => $this->loaded ? $this->accountOptions() : [],
            'manualAccountOptions' => $this->loaded ? $this->manualAccountOptions() : [],
            'classifications'      => JournalClassification::options(),
            'totals'               => $this->totals(),
            'balanced'             => $this->isBalanced(),
        ]);
    }
}
