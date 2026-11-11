<?php

namespace App\Http\Livewire\Concerns;

trait WithCsvImport
{
    use HandlesCsvImportErrors;

    // =====================================================================
    // Listeners
    // =====================================================================

    public function openImportModal(): void
    {
        $this->authorize('import', $this->importAuthorizable());

        $this->emit('loadCsvImportModal', [
            'title'   => $this->importTitle(),
            'columns' => implode(', ', $this->importColumns()),
        ]);
    }

    public function handleCsvImport(string $path): void
    {
        $this->authorize('import', $this->importAuthorizable());

        try {
            $result = $this->importFile($path);

            if (! empty($result['skipped'])) {
                $this->emit('notify', 'warning',
                    "{$result['imported']} imported, "
                    . count($result['skipped']) . " skipped. First issue: "
                    . $result['skipped'][0]);
            } else {
                $this->emit('notify', 'success',
                    "{$result['imported']} " . $this->importLabel() . " imported successfully.");
            }
        } catch (\Exception $e) {
            $this->emit('notify', 'error', 'Import failed: ' . $this->formatImportError($e));
        }
    }

    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('export', $this->importAuthorizable());

        $csv = $this->exportCsv();

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $this->importLabel() . '-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $csv = $this->importTemplate();

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $this->importLabel() . '-import-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // =====================================================================
    // Abstracts
    // =====================================================================

    abstract protected function importAuthorizable(): string;

    abstract protected function importTitle(): string;

    abstract protected function importColumns(): array;

    abstract protected function importLabel(): string;

    abstract protected function importFile(string $path): array;

    abstract protected function exportCsv(): string;

    abstract protected function importTemplate(): string;
}
