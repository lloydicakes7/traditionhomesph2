<?php

namespace App\Http\Livewire\Admin\Homeowners\Property;

use App\Http\Livewire\Concerns\WithCsvImport;
use App\Http\Livewire\AdminComponent;
use App\Models\Property;
use App\Services\Csv\PropertyCsvService;

/**
 * Properties page — the CSV toolbar: export, template download, and the
 * import flow through the shared Admin\Modals\CsvImportModal.
 *
 * Lives in its own component so importing/exporting never re-renders the
 * table; the modal's confirm event is handled here.
 */
class PropertyCsv extends AdminComponent
{
    use WithCsvImport;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'csv-import-confirmed' => 'handleCsvImport',
    ];

    /** @var PropertyCsvService */
    protected $propertyCsvService;

    public function boot(PropertyCsvService $propertyCsvService)
    {
        $this->propertyCsvService = $propertyCsvService;
    }

    // =====================================================================
    // WithCsvImport hooks
    // =====================================================================

    protected function importAuthorizable(): string
    {
        return Property::class;
    }

    protected function importTitle(): string
    {
        return 'Import Properties from CSV';
    }

    protected function importColumns(): array
    {
        return $this->propertyCsvService->getColumnMap();
    }

    protected function importLabel(): string
    {
        return 'properties';
    }

    protected function importFile(string $path): array
    {
        return $this->propertyCsvService->import($path);
    }

    protected function exportCsv(): string
    {
        return $this->propertyCsvService->export();
    }

    protected function importTemplate(): string
    {
        return $this->propertyCsvService->getImportTemplate();
    }
}
