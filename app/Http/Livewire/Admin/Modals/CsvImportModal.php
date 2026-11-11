<?php

namespace App\Http\Livewire\Admin\Modals;

use App\Http\Livewire\AdminModalComponent;
use Livewire\WithFileUploads;

/**
 * The shared "Import from CSV" dialog.
 *
 * Its opener configures it — the title and the columns the page expects — so it
 * is one of the dialogs the server opens rather than one `data-toggle` opens:
 * the page emits `loadCsvImportModal` with the configuration, and `load()` asks
 * Bootstrap for the shell (`modal-open`, handled by public/js/modal-loader.js)
 * once the dialog is ready. Confirming hands the validated upload's temp path
 * back to the page as `csv-import-confirmed`, then closes itself with
 * `modal-close`; `closeCsvImportModal` (fired on `hidden.bs.modal`) resets it.
 */
class CsvImportModal extends AdminModalComponent
{
    use WithFileUploads;

    /** @var string Dialog title, set by the opener */
    public $title = 'Import from CSV';

    /** @var string Comma-separated columns the page's importer expects */
    public $columns = '';

    /** @var mixed Uploaded CSV file */
    public $importFile;

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadCsvImportModal'  => 'load',
        'openCsvImportModal'  => 'refreshModal',
        'closeCsvImportModal' => 'closeModal',
    ];

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    /**
     * Show the dialog configured by the opener.
     *
     * @param  array  $config  {title?, columns?}
     */
    public function load(array $config = []): void
    {
        $this->title      = $config['title'] ?? $this->title;
        $this->columns    = $config['columns'] ?? '';
        $this->importFile = null;

        $this->resetValidation();

        $this->markLoaded();

        $this->dispatchBrowserEvent('modal-open', ['id' => 'csvImportModal']);
    }

    /**
     * Drop the upload when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->importFile = null;

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Confirm
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->validate();

        $path = $this->importFile->getRealPath();

        $this->emit('csv-import-confirmed', $path);

        $this->dispatchBrowserEvent('modal-close', ['id' => 'csvImportModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.modals.csv-import-modal');
    }
}
