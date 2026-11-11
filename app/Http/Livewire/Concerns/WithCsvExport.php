<?php

namespace App\Http\Livewire\Concerns;

/**
 * Shared CSV export for the admin panel's list pages.
 *
 * A page that shows a list of records can offer it as a download: the page
 * supplies the CSV (usually by asking its service to build it with
 * \App\Utility\CsvHelper) and this trait streams it back with the right
 * filename and content type. Authorization stays with the page — the export is
 * checked against the same Policy ability as the page it comes from.
 *
 * The page names the download: every adopter states `exportFileName()`, because
 * the filename follows the page's own entity rather than the trait's.
 */
trait WithCsvExport
{
    /**
     * Stream the current list as a CSV download.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize($this->exportAbility(), $this->exportAuthorizable());

        $csv = $this->exportCsv();

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $this->exportFileName(), [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * The Policy ability the export is checked against.
     *
     * The default is the ordinary `export`; a page whose model is guarded by
     * more than one permission module names its own ability, so two lists over
     * the same class can be granted apart.
     */
    protected function exportAbility(): string
    {
        return 'export';
    }

    /**
     * Model class the export Policy ability is checked against.
     */
    abstract protected function exportAuthorizable(): string;

    /**
     * The CSV for the page's current filters.
     */
    abstract protected function exportCsv(): string;

    /**
     * The name the download is saved under.
     */
    abstract protected function exportFileName(): string;
}
