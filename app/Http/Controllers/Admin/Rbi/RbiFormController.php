<?php

namespace App\Http\Controllers\Admin\Rbi;

use App\Enums\RbiSemester;
use App\Http\Controllers\Controller;
use App\Models\Household\Household;
use App\Models\Resident;
use App\Queries\Rbi\FormAQuery;
use App\Queries\Rbi\FormBQuery;
use App\Queries\Rbi\FormCQuery;
use App\Queries\Rbi\FormPickerQuery;
use App\Queries\Rbi\SheetQuery;
use App\Utility\PdfBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RbiFormController extends Controller
{
    const GATE = 'rbi.viewAny';

    // ===== Pages =====

    public function formA(Request $request, FormPickerQuery $pickers)
    {
        $this->authorize(self::GATE);

        $household = $this->selected(Household::class, $request->query('household'));

        if ($household) {
            $this->authorize('printRbi', $household);
        }

        return view('admin.rbi.form-a', [
            'title'       => __('rbi.form_a.title'),
            'households'  => $pickers->households(),
            'selected'    => $household,
            'pdfUrl'      => $household ? route('rbi.form-a.sheet', $household) : null,
            'downloadUrl' => $household ? route('rbi.form-a.download', $household) : null,
        ]);
    }

    public function formB(Request $request, FormPickerQuery $pickers)
    {
        $this->authorize(self::GATE);

        $resident = $this->selected(Resident::class, $request->query('resident'));

        if ($resident) {
            $this->authorize('printRbi', $resident);
        }

        return view('admin.rbi.form-b', [
            'title'       => __('rbi.form_b.title'),
            'residents'   => $pickers->residents(),
            'selected'    => $resident,
            'pdfUrl'      => $resident ? route('rbi.form-b.sheet', $resident) : null,
            'downloadUrl' => $resident ? route('rbi.form-b.download', $resident) : null,
        ]);
    }

    public function formC(Request $request)
    {
        $this->authorize('rbi.reports');

        $params = $this->reportParams($request);
        $query = $this->queryParams($params);

        return view('admin.rbi.form-c', [
            'title'       => __('rbi.form_c.title'),
            'pdfUrl'      => route('rbi.form-c.sheet', $query),
            'downloadUrl' => route('rbi.form-c.download', $query),
            'filters'     => $params,
            'semesters'   => RbiSemester::labels(),
        ]);
    }

    // ===== Sheets =====

    public function formASheet(Household $household, FormAQuery $query)
    {
        $this->authorize('printRbi', $household);

        return $this->preview('pdf.rbi-form-a', __('rbi.form_a.title'), $query->sheet($household), 'legal', 'landscape');
    }

    public function formADownload(Household $household, FormAQuery $query)
    {
        $this->authorize('printRbi', $household);

        return $this->download(
            'pdf.rbi-form-a',
            __('rbi.form_a.title'),
            $query->sheet($household),
            $this->filename('RBI Form A', $household),
            'legal',
            'landscape'
        );
    }

    public function formBSheet(Resident $resident, FormBQuery $query)
    {
        $this->authorize('printRbi', $resident);

        return $this->preview('pdf.rbi-form-b', __('rbi.form_b.title'), $query->sheet($resident));
    }

    public function formBDownload(Resident $resident, FormBQuery $query)
    {
        $this->authorize('printRbi', $resident);

        return $this->download(
            'pdf.rbi-form-b',
            __('rbi.form_b.title'),
            $query->sheet($resident),
            $this->filename('RBI Form B', $resident)
        );
    }

    public function formCSheet(Request $request, FormCQuery $query)
    {
        $this->authorize('rbi.reports');

        $params = $this->reportParams($request);

        return $this->preview(
            'pdf.rbi-form-c',
            __('rbi.form_c.title'),
            $query->sheet($params['year'], $params['semester']),
            'letter'
        );
    }

    public function formCDownload(Request $request, FormCQuery $query)
    {
        $this->authorize('rbi.reports');

        $params = $this->reportParams($request);

        return $this->download(
            'pdf.rbi-form-c',
            __('rbi.form_c.title'),
            $query->sheet($params['year'], $params['semester']),
            'RBI-Form-C-' . $params['year'] . '-' . $params['semester']->value,
            'letter'
        );
    }

    // ===== Downloads =====

    /**
     * The record a picker asked for, when it exists.
     */
    private function selected(string $model, $id): ?Model
    {
        return $id ? $model::find((int) $id) : null;
    }

    /**
     * The sheet itself, streamed so the browser shows it in its own PDF viewer.
     */
    private function preview(string $view, string $title, array $data, string $paper = 'a4', string $orientation = 'portrait')
    {
        return PdfBuilder::stream(
            $view,
            $this->payload($title, $data),
            PdfBuilder::filename($title),
            $paper,
            $orientation
        );
    }

    private function download(string $view, string $title, array $data, string $filename, string $paper = 'a4', string $orientation = 'portrait')
    {
        return PdfBuilder::download(
            $view,
            $this->payload($title, $data),
            PdfBuilder::filename($filename),
            $paper,
            $orientation
        );
    }

    private function payload(string $title, array $data): array
    {
        return app(SheetQuery::class)->payload($title, $data);
    }

    private function filename(string $form, $record): string
    {
        return $form . ' ' . ($record->full_name ?? $record->rbi_name ?? $record->id);
    }

    // ===== Helpers =====

    /**
     * The semester to report, from the query string.
     *
     * @return array{year: int, semester: \App\Enums\RbiSemester}
     */
    private function reportParams(Request $request): array
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'semester' => ['nullable', 'in:first,second'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);
        $semester = RbiSemester::tryFrom($validated['semester'] ?? (now()->month <= 6 ? 'first' : 'second'))
            ?: RbiSemester::FIRST();

        return ['year' => $year, 'semester' => $semester];
    }

    /**
     * The same report as a query string, so a route never carries the enum
     * object itself — it would be serialised as its own fields.
     *
     * @param  array{year: int, semester: \App\Enums\RbiSemester}  $params
     * @return array{year: int, semester: string}
     */
    private function queryParams(array $params): array
    {
        return ['year' => $params['year'], 'semester' => $params['semester']->value];
    }
}
