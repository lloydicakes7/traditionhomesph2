<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident;

use App\Http\Livewire\Concerns\WithCsvImport;
use App\Http\Livewire\AdminComponent;
use App\Models\Resident;
use App\Services\Csv\ResidentCsvService;

/**
 * Residents page — the CSV toolbar: export, template download, and the
 * import flow through the shared Admin\Modals\CsvImportModal.
 *
 * Lives in its own component so importing/exporting never re-renders the
 * table; the modal's confirm event is handled here.
 */
class ResidentCsv extends AdminComponent
{
    use WithCsvImport;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'csv-import-confirmed' => 'handleCsvImport',
    ];

    /** @var ResidentCsvService */
    protected $residentCsvService;

    public function boot(ResidentCsvService $residentCsvService)
    {
        $this->residentCsvService = $residentCsvService;
    }

    // =====================================================================
    // WithCsvImport hooks
    // =====================================================================

    protected function importAuthorizable(): string
    {
        return Resident::class;
    }

    protected function importTitle(): string
    {
        return 'Import Residents from CSV';
    }

    protected function importColumns(): array
    {
        return $this->residentCsvService->getColumnMap();
    }

    protected function importLabel(): string
    {
        return 'residents';
    }

    protected function importFile(string $path): array
    {
        return $this->residentCsvService->import($path, auth('web')->user());
    }

    protected function exportCsv(): string
    {
        return $this->residentCsvService->export();
    }

    protected function importTemplate(): string
    {
        return $this->residentCsvService->getImportTemplate();
    }
}
