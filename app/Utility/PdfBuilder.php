<?php

namespace App\Utility;

use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rendering a Blade view into a PDF, the way the association prints one.
 *
 * Technical only: it knows how to turn a `pdf/` view into a document, never
 * what the document says. Every printable record and report goes through it, so
 * the paper size, the paper orientation and the filename convention are decided
 * in one place rather than at each call site.
 *
 * The views it renders are the ones under `resources/views/pdf/`, which are
 * standalone documents styled for dompdf — they never extend the app layout.
 */
class PdfBuilder
{
    /**
     * The memory a document may use while it is built.
     *
     * dompdf lays the entire document out in memory before it writes a single
     * page, so a report over a large list needs far more than the default
     * server limit. `render()` raises the limit to this for the build.
     */
    const MEMORY_LIMIT = '1024M';

    /** The seconds a document may take to build. */
    const TIME_LIMIT = 300;

    /**
     * Build a PDF from a view and hand it back as a download.
     *
     * @param  string  $view      The `pdf.` view to render
     * @param  array   $data      What the view reads
     * @param  string  $filename  The name the download is saved under
     * @param  string|array  $paper  Paper size, e.g. "a4", "letter", or [0, 0, width, height] in points
     * @param  string  $orientation  "portrait" or "landscape"
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function download(
        string $view,
        array $data = [],
        string $filename = 'document.pdf',
        $paper = 'a4',
        string $orientation = 'portrait'
    ): Response {
        return self::render(function () use ($view, $data, $filename, $paper, $orientation) {
            return Pdf::loadView($view, $data)
                ->setPaper($paper, $orientation)
                ->download($filename);
        });
    }

    /**
     * Build a PDF and hand it back for the browser to show rather than save.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function stream(
        string $view,
        array $data = [],
        string $filename = 'document.pdf',
        $paper = 'a4',
        string $orientation = 'portrait'
    ): Response {
        return self::render(function () use ($view, $data, $filename, $paper, $orientation) {
            return Pdf::loadView($view, $data)
                ->setPaper($paper, $orientation)
                ->stream($filename);
        });
    }

    /**
     * Run a document's build with the resources dompdf needs.
     *
     * dompdf builds the whole document before it writes any of it, so a large
     * list needs far more memory and time than a normal request is allowed —
     * over the default it dies part-way with a 500 and the browser shows
     * nothing. The budget is only ever raised, never lowered, so a server or a
     * CLI run that already gives more keeps it, and it is left in place because
     * it cannot be lowered again while the built document is still in memory.
     *
     * @param  callable():Response  $build
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private static function render(callable $build): Response
    {
        $memory = self::bytes(ini_get('memory_limit'));

        if ($memory !== -1 && $memory < self::bytes(self::MEMORY_LIMIT)) {
            ini_set('memory_limit', self::MEMORY_LIMIT);
        }

        if ((int) ini_get('max_execution_time') !== 0
            && (int) ini_get('max_execution_time') < self::TIME_LIMIT) {
            set_time_limit(self::TIME_LIMIT);
        }

        return $build();
    }

    /**
     * An ini size ("512M", "1G", "-1") as bytes, so two limits can be
     * compared; `-1` (unlimited) is kept as a negative sentinel.
     */
    private static function bytes($limit): int
    {
        $limit = trim((string) $limit);

        if ($limit === '' || $limit === '-1') {
            return -1;
        }

        $value = (int) $limit;

        switch (strtolower(substr($limit, -1))) {
            case 'g':
                return $value * 1073741824;
            case 'm':
                return $value * 1048576;
            case 'k':
                return $value * 1024;
        }

        return $value;
    }

    /**
     * A document's stylesheet, read from `public/` so it can be inlined.
     *
     * DomPDF renders a standalone document, so a stylesheet reaches it only
     * when the view prints it; reading it here keeps every document's view and
     * its CSS in one place, and a missing file renders unstyled rather than
     * failing the build.
     */
    public static function stylesheet(string $relativePath): string
    {
        $path = public_path($relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    /**
     * A record's name turned into a filename-safe stem: "Voucher JE-2026-0001".
     */
    public static function filename(string $stem): string
    {
        $stem = preg_replace('/[^A-Za-z0-9\-_ ]+/', '', $stem);

        return trim(preg_replace('/\s+/', '-', (string) $stem), '-') . '.pdf';
    }
}
