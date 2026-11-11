<?php

namespace App\Http\Livewire\Admin\Homeowners\Household;

use App\Http\Livewire\Concerns\WithCsvImport;
use App\Http\Livewire\AdminComponent;
use App\Models\Household\Household;
use App\Services\Csv\HouseholdCsvService;

class HouseholdCsv extends AdminComponent
{
    use WithCsvImport;

    protected $listeners = [
        'csv-import-confirmed' => 'handleCsvImport',
    ];

    /** @var HouseholdCsvService */
    protected $householdCsvService;

    public function boot(HouseholdCsvService $householdCsvService)
    {
        $this->householdCsvService = $householdCsvService;
    }

    // =====================================================================
    // WithCsvImport hooks
    // =====================================================================

    protected function importAuthorizable(): string
    {
        return Household::class;
    }

    protected function importTitle(): string
    {
        return 'Import Households from CSV';
    }

    protected function importColumns(): array
    {
        return $this->householdCsvService->getColumnMap();
    }

    protected function importLabel(): string
    {
        return 'households';
    }

    protected function importFile(string $path): array
    {
        return $this->householdCsvService->import($path);
    }

    protected function exportCsv(): string
    {
        return $this->householdCsvService->export();
    }

    protected function importTemplate(): string
    {
        return $this->householdCsvService->getImportTemplate();
    }
}
