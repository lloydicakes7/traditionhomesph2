<?php

namespace App\Http\Livewire\Concerns;

/**
 * Shared CSV download for the Reports suite's pages.
 *
 * A report is a view of other records rather than a record of its own, so its
 * export is authorized by the Reports suite's own gate rather than by a Policy
 * ability on a model — the one thing that makes a report's export different
 * from the lists' (see \App\Http\Livewire\Concerns\WithCsvExport).
 *
 * A report component supplies the CSV for whatever it is currently showing, and
 * names itself so the download is recognizable.
 */
trait ExportsReportCsv
{
    /**
     * Stream the report's current contents as a CSV download.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('report.export');

        $csv = $this->exportCsv();

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $this->reportKey() . '-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * The CSV for the report's current filters.
     */
    abstract protected function exportCsv(): string;

    /**
     * The report's own short name, used as the download's filename.
     */
    abstract protected function reportKey(): string;
}
