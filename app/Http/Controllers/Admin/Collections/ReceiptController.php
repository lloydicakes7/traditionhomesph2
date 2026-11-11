<?php

namespace App\Http\Controllers\Admin\Collections;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Queries\Collections\Payments\ReceiptQuery;
use App\Queries\Settings\PrintSettingsQuery;
use App\Services\Collections\ReceiptService;

class ReceiptController extends Controller
{
    // ===== Downloads =====

    public function show(Payment $payment, ReceiptQuery $query, ReceiptService $receipts)
    {
        return $receipts->preview($this->receipt($payment, $query), app(PrintSettingsQuery::class)->letterhead());
    }

    public function download(Payment $payment, ReceiptQuery $query, ReceiptService $receipts)
    {
        return $receipts->download($this->receipt($payment, $query), app(PrintSettingsQuery::class)->letterhead());
    }

    public function showThermal(Payment $payment, ReceiptQuery $query, ReceiptService $receipts)
    {
        return $receipts->previewThermal($this->receipt($payment, $query));
    }

    public function downloadThermal(Payment $payment, ReceiptQuery $query, ReceiptService $receipts)
    {
        return $receipts->downloadThermal($this->receipt($payment, $query));
    }

    // ===== Helpers =====

    private function receipt(Payment $payment, ReceiptQuery $query): array
    {
        $this->authorize('view', $payment);

        abort_unless($payment->is_recorded && $payment->receipt_no, 404);

        return $query->build($payment);
    }
}
