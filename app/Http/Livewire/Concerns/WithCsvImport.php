<?php

namespace App\Http\Livewire\Concerns;

/**
 * The CSV toolbar every homeowners page shares: open the import dialog, read
 * the uploaded file, and report what happened to each row.
 *
 * The page itself stays quiet: one short notice, and the row-by-row detail
 * goes to the shared results dialog (`admin.modals.csv-import-result-modal`).
 * A service throws InvalidArgumentException with a sentence fit for the screen
 * when the file itself cannot be read; anything else is reported without
 * exposing the database's own wording.
 */
trait WithCsvImport
{
    // ===== Actions =====

    public function openImportModal(): void
    {
        $this->authorize('import', $this->importAuthorizable());

        $this->emit('loadCsvImportModal', [
            'title'   => $this->importTitle(),
            'columns' => $this->importColumns(),
        ]);
    }

    public function handleCsvImport(string $path): void
    {
        $this->authorize('import', $this->importAuthorizable());

        try {
            $result = $this->importFile($path);
        } catch (\InvalidArgumentException $e) {
            $this->reportImport(0, [], $e->getMessage());

            return;
        } catch (\Exception $e) {
            $this->reportImport(0, [], 'The file could not be read. Use the template and try again.');

            return;
        }

        $this->reportImport($result['imported'], $result['skipped']);
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

    // ===== Helpers =====

    /**
     * Hand the row-by-row detail to the results dialog and send one notice.
     *
     * @param  array<int, string>  $skipped
     */
    private function reportImport(int $imported, array $skipped, ?string $error = null): void
    {
        $this->emit('csv-import-report', [
            'title'    => ucfirst($this->importLabel()) . ' import',
            'imported' => $imported,
            'skipped'  => $skipped,
            'error'    => $error,
        ]);

        if ($error !== null) {
            $this->emit('notify', 'error', $error);

            return;
        }

        $this->emit('notify', $skipped === [] ? 'success' : 'warning', $this->importSummary($imported, $skipped));
    }

    /**
     * One short sentence for the notice: how many rows went in.
     *
     * @param  array<int, string>  $skipped
     */
    private function importSummary(int $imported, array $skipped): string
    {
        if ($skipped === []) {
            return $imported === 1 ? '1 row imported.' : "{$imported} rows imported.";
        }

        $skippedCount = count($skipped);
        $total = $imported + $skippedCount;

        return "Imported {$imported} of {$total} rows. {$skippedCount} "
            . ($skippedCount === 1 ? 'row' : 'rows') . ' skipped.';
    }

    // ===== Abstracts =====

    abstract protected function importAuthorizable(): string;

    abstract protected function importTitle(): string;

    abstract protected function importColumns(): array;

    abstract protected function importLabel(): string;

    abstract protected function importFile(string $path): array;

    abstract protected function exportCsv(): string;

    abstract protected function importTemplate(): string;
}
