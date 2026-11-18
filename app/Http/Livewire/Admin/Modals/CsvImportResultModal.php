<?php

namespace App\Http\Livewire\Admin\Modals;

use App\Http\Livewire\AdminModalComponent;

/**
 * The shared "Import results" dialog.
 *
 * The importing page sends `csv-import-report` once the sheet has been read;
 * the dialog then lists every skipped row with its reason, or the reason the
 * whole file was refused, so the officer knows what to correct.
 */
class CsvImportResultModal extends AdminModalComponent
{
    public const MODAL_ID = 'csvImportResultModal';

    /** @var string Dialog title, set by the importing page */
    public $title = 'Import results';

    /** @var int Rows written to the database */
    public $imported = 0;

    /** @var array<int, string> Row number and reason for each skipped row */
    public $skipped = [];

    /** @var string|null Why the whole file was refused */
    public $error = null;

    /** @var array<string, string> */
    protected $listeners = [
        'csv-import-report' => 'show',
    ];

    /**
     * Show the results the importing page just reported.
     *
     * @param  array{title?: string, imported?: int, skipped?: array<int, string>, error?: string|null}  $report
     */
    public function show(array $report = []): void
    {
        $this->title    = $report['title'] ?? $this->title;
        $this->imported = (int) ($report['imported'] ?? 0);
        $this->skipped  = array_values($report['skipped'] ?? []);
        $this->error    = $report['error'] ?? null;

        $this->markLoaded();

        $this->dispatchBrowserEvent('modal-open', ['id' => self::MODAL_ID]);
    }

    protected function resetModal(): void
    {
        $this->imported = 0;
        $this->skipped  = [];
        $this->error    = null;

        parent::resetModal();
    }

    public function render()
    {
        return view('livewire.admin.modals.csv-import-result-modal');
    }

    // ===== Helpers =====

    /**
     * One short sentence describing the outcome.
     */
    public function summary(): string
    {
        if ($this->error !== null) {
            return 'Nothing was imported.';
        }

        $total = $this->imported + count($this->skipped);

        if ($this->skipped === []) {
            return $this->imported === 1 ? '1 row imported.' : "{$this->imported} rows imported.";
        }

        return "{$this->imported} of {$total} rows imported.";
    }

    /**
     * The Bootstrap alert colour the outcome deserves.
     */
    public function outcome(): string
    {
        if ($this->error !== null) {
            return 'danger';
        }

        return $this->skipped === [] ? 'success' : 'warning';
    }
}
