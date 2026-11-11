<?php

namespace App\Services\Collections;

use App\Utility\PdfBuilder;
use Symfony\Component\HttpFoundation\Response;

class ReceiptService
{
    const VIEW = 'pdf.receipt';

    const THERMAL_VIEW = 'pdf.receipt-thermal';

    const STYLESHEET = 'css/receipt.css';

    /** 80 mm roll in points (80 mm ÷ 25.4 × 72). */
    const THERMAL_WIDTH = 226.77;

    // ===== Operations =====

    public function preview(array $receipt, array $letterhead): Response
    {
        return PdfBuilder::stream(
            self::VIEW,
            $this->data($receipt, $letterhead),
            $this->filename($receipt),
            $this->paper($letterhead)
        );
    }

    public function download(array $receipt, array $letterhead): Response
    {
        return PdfBuilder::download(
            self::VIEW,
            $this->data($receipt, $letterhead),
            $this->filename($receipt),
            $this->paper($letterhead)
        );
    }

    public function previewThermal(array $receipt): Response
    {
        return PdfBuilder::stream(
            self::THERMAL_VIEW,
            $this->thermalData($receipt),
            $this->filename($receipt),
            $this->thermalPaper($receipt)
        );
    }

    public function downloadThermal(array $receipt): Response
    {
        return PdfBuilder::download(
            self::THERMAL_VIEW,
            $this->thermalData($receipt),
            $this->filename($receipt),
            $this->thermalPaper($receipt)
        );
    }

    // ===== Helpers =====

    private function data(array $receipt, array $letterhead): array
    {
        return [
            'receipt'    => $receipt,
            'letterhead' => $letterhead,
            'styles'     => $this->styles(),
            'printedAt'  => now(),
        ];
    }

    private function thermalData(array $receipt): array
    {
        return [
            'receipt'   => $receipt,
            'hoa_name'  => (string) config('thha.hoa_name'),
            'printedAt' => now(),
        ];
    }

    /**
     * A roll has no fixed height, and dompdf cannot measure one for us, so the
     * page is sized from the lines the receipt is expected to take.
     */
    private function thermalPaper(array $receipt): array
    {
        $lines = 34 + count($receipt['items']);
        $height = max(400, $lines * 10);

        return [0, 0, self::THERMAL_WIDTH, $height];
    }

    private function styles(): string
    {
        $path = public_path(self::STYLESHEET);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function filename(array $receipt): string
    {
        return PdfBuilder::filename('Official Receipt ' . $receipt['receipt_no']);
    }

    private function paper(array $letterhead): string
    {
        return (string) ($letterhead['paper'] ?? 'a4');
    }
}
