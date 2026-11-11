<?php

namespace App\Http\Controllers\Portal\Support;

use App\Http\Controllers\Controller;
use App\Models\Request as RequestModel;
use App\Queries\Settings\PrintSettingsQuery;
use App\Queries\Support\TransactionSlipQuery;
use App\Utility\PdfBuilder;

/**
 * The transaction slip a homeowner brings to the office after filing a request.
 *
 * The slip is available once the office has approved the request: it is what
 * the homeowner brings to the counter to pay and collect. A homeowner prints
 * their own approved request's slip and never another household's
 * (RequestPolicy::downloadSlipOwn).
 */
class RequestSlipController extends Controller
{
    const STYLESHEET = 'css/slip.css';

    /**
     * Download the slip for this request.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(int $id, TransactionSlipQuery $slips, PrintSettingsQuery $settings)
    {
        $request = RequestModel::findOrFail($id);

        $this->authorize('downloadSlipOwn', $request);

        $letterhead = $settings->letterhead();

        return PdfBuilder::download(
            'pdf.slip',
            [
                'slip'       => $slips->build($request),
                'letterhead' => $letterhead,
                'styles'     => PdfBuilder::stylesheet(self::STYLESHEET),
                'printedAt'  => now(),
            ],
            PdfBuilder::filename(__('slips.file_name') . ' ' . $request->reference),
            (string) ($letterhead['paper'] ?? 'a4')
        );
    }
}
