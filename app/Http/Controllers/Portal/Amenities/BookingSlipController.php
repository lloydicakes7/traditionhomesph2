<?php

namespace App\Http\Controllers\Portal\Amenities;

use App\Http\Controllers\Controller;
use App\Models\Amenity\AmenityBooking;
use App\Queries\Settings\PrintSettingsQuery;
use App\Queries\Support\TransactionSlipQuery;
use App\Utility\PdfBuilder;

/**
 * The transaction slip a homeowner brings to the office after booking an amenity.
 *
 * Available once the office has approved the reservation: it is what the
 * homeowner brings to the counter to pay and use the facility. Ownership and
 * approval are the whole rule (see AmenityBookingPolicy::downloadSlipOwn).
 */
class BookingSlipController extends Controller
{
    const STYLESHEET = 'css/slip.css';

    /**
     * Download the slip for this reservation.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(int $id, TransactionSlipQuery $slips, PrintSettingsQuery $settings)
    {
        $booking = AmenityBooking::findOrFail($id);

        $this->authorize('downloadSlipOwn', $booking);

        $letterhead = $settings->letterhead();

        return PdfBuilder::download(
            'pdf.slip',
            [
                'slip'       => $slips->build($booking),
                'letterhead' => $letterhead,
                'styles'     => PdfBuilder::stylesheet(self::STYLESHEET),
                'printedAt'  => now(),
            ],
            PdfBuilder::filename(__('slips.file_name') . ' ' . $booking->reference),
            (string) ($letterhead['paper'] ?? 'a4')
        );
    }
}
