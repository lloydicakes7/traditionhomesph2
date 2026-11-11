<?php

namespace App\Http\Controllers\Portal\Rbi;

use App\Http\Controllers\Controller;
use App\Models\Household\Household;
use App\Models\Resident;
use App\Queries\Rbi\FormAQuery;
use App\Queries\Rbi\FormBQuery;
use App\Queries\Rbi\SheetQuery;
use App\Utility\PdfBuilder;

class RbiFormController extends Controller
{
    private SheetQuery $sheet;

    public function __construct(SheetQuery $sheet)
    {
        $this->sheet = $sheet;
    }

    public function index()
    {
        return view('portal.rbi.index');
    }

    public function formA(Household $household)
    {
        $this->authorize('printRbiOwn', $household);

        return $this->page(
            __('rbi.form_a.title'),
            route('portal.rbi.form-a.sheet', $household),
            route('portal.rbi.form-a.download', $household)
        );
    }

    public function formASheet(Household $household, FormAQuery $query)
    {
        $this->authorize('printRbiOwn', $household);

        return $this->preview('pdf.rbi-form-a', __('rbi.form_a.title'), $query->sheet($household), 'legal', 'landscape');
    }

    public function formADownload(Household $household, FormAQuery $query)
    {
        $this->authorize('printRbiOwn', $household);

        return $this->download(
            'pdf.rbi-form-a',
            __('rbi.form_a.title'),
            $query->sheet($household),
            $this->filename('RBI Form A', $household),
            'legal',
            'landscape'
        );
    }

    public function formB(Resident $resident)
    {
        $this->authorize('printRbiOwn', $resident);

        return $this->page(
            __('rbi.form_b.title'),
            route('portal.rbi.form-b.sheet', $resident),
            route('portal.rbi.form-b.download', $resident)
        );
    }

    public function formBSheet(Resident $resident, FormBQuery $query)
    {
        $this->authorize('printRbiOwn', $resident);

        return $this->preview('pdf.rbi-form-b', __('rbi.form_b.title'), $query->sheet($resident));
    }

    public function formBDownload(Resident $resident, FormBQuery $query)
    {
        $this->authorize('printRbiOwn', $resident);

        return $this->download(
            'pdf.rbi-form-b',
            __('rbi.form_b.title'),
            $query->sheet($resident),
            $this->filename('RBI Form B', $resident)
        );
    }

    private function page(string $title, string $pdfUrl, string $downloadUrl)
    {
        return view('portal.rbi.preview', [
            'title'       => $title,
            'pdfUrl'      => $pdfUrl,
            'downloadUrl' => $downloadUrl,
        ]);
    }

    /**
     * The sheet itself, streamed so the browser shows it in its own PDF viewer.
     */
    private function preview(string $view, string $title, array $data, string $paper = 'a4', string $orientation = 'portrait')
    {
        return PdfBuilder::stream(
            $view,
            $this->sheet->payload($title, $data),
            PdfBuilder::filename($title),
            $paper,
            $orientation
        );
    }

    private function download(string $view, string $title, array $data, string $filename, string $paper = 'a4', string $orientation = 'portrait')
    {
        return PdfBuilder::download(
            $view,
            $this->sheet->payload($title, $data),
            PdfBuilder::filename($filename),
            $paper,
            $orientation
        );
    }

    private function filename(string $form, $record): string
    {
        return $form . ' ' . ($record->full_name ?? $record->rbi_name ?? $record->id);
    }
}
