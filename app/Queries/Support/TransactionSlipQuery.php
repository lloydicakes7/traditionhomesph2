<?php

namespace App\Queries\Support;

use App\Models\Amenity\AmenityBooking;
use App\Models\Request as RequestModel;
use Illuminate\Database\Eloquent\Model;

/**
 * The data one transaction slip prints, for a request or an amenity booking.
 *
 * A slip is the homeowner's proof that they filed something, so it says only
 * what the office needs to find the record at the counter: the reference, what
 * was filed, who filed it, which unit, when, what it costs and where it stands.
 * It is deliberately not a receipt and not the issued document — neither the
 * office's decision nor a payment is claimed here.
 *
 * `build()` returns a plain array the one slip view renders: the heading, the
 * owner block, and the rows that differ between a request and a booking. It
 * reads and never writes, and it carries nothing private (no PhilSys number,
 * birth date, income, mother's maiden name or contact details).
 */
class TransactionSlipQuery
{
    // ===== Build =====

    /**
     * Build the slip for a request or a booking.
     *
     * @return array<string, mixed>
     */
    public function build(Model $record): array
    {
        if ($record instanceof RequestModel) {
            return $this->request($record);
        }

        if ($record instanceof AmenityBooking) {
            return $this->booking($record);
        }

        abort(404);
    }

    // ===== Records =====

    /**
     * A request's slip: the document asked for, its purpose and its fee.
     *
     * @return array<string, mixed>
     */
    private function request(RequestModel $request): array
    {
        $request->loadMissing(['resident.user.household.property', 'bill']);

        $rows = [
            ['label' => __('slips.request.document'), 'value' => $request->type_label],
            ['label' => __('slips.request.details'), 'value' => $request->summary],
        ];

        if ($request->bill) {
            $rows[] = ['label' => __('slips.request.bill'), 'value' => $request->bill->reference];
            $rows[] = ['label' => __('slips.request.fee'), 'value' => $request->bill->total_display];
        }

        return [
            'reference'    => $request->reference ?: '—',
            'kind'         => $request->type_label,
            'homeowner'    => $request->resident_label,
            'unit'         => $this->unit($request->resident ? $request->resident->user : null),
            'filed_at'     => $this->stamp($request->created_at),
            'rows'         => $rows,
            'status'       => $request->status_label,
            'status_theme' => $request->status_badge_theme,
            'note'         => __('slips.note'),
        ];
    }

    /**
     * A booking's slip: the facility, its window, and what it costs.
     *
     * @return array<string, mixed>
     */
    private function booking(AmenityBooking $booking): array
    {
        $booking->loadMissing(['amenity', 'user.household.property', 'bill']);

        $rows = [
            ['label' => __('slips.booking.amenity'), 'value' => $booking->amenity->name ?? '—'],
            ['label' => __('slips.booking.schedule'), 'value' => $booking->date_time_display],
        ];

        if ($booking->purpose) {
            $rows[] = ['label' => __('slips.booking.purpose'), 'value' => $booking->purpose];
        }

        $rows[] = ['label' => __('slips.booking.amount'), 'value' => $booking->amount_display];
        $rows[] = ['label' => __('slips.booking.deposit'), 'value' => $booking->deposit_display];

        if ($booking->bill) {
            $rows[] = ['label' => __('slips.booking.bill'), 'value' => $booking->bill->reference];
        }

        return [
            'reference'    => $booking->reference ?: '—',
            'kind'         => $booking->amenity->name ?? '—',
            'homeowner'    => $booking->user?->resident?->full_name ?? (string) $booking->guest_name,
            'unit'         => $this->unit($booking->user),
            'filed_at'     => $this->stamp($booking->created_at),
            'rows'         => $rows,
            'status'       => $booking->status_enum ? $booking->status_enum->label() : (string) $booking->status,
            'status_theme' => $booking->status_enum ? $booking->status_enum->badgeTheme() : 'secondary',
            'note'         => __('slips.note'),
        ];
    }

    // ===== Helpers =====

    /**
     * The unit the homeowner's account occupies: "Blk 7 Lot 12", or a dash
     * while the account holds no household.
     */
    private function unit(?object $user): string
    {
        return $user?->household?->property?->block_lot_label ?? '—';
    }

    /**
     * When the record was filed, as the slip writes it.
     */
    private function stamp($createdAt): string
    {
        return $createdAt ? $createdAt->format('M j, Y g:i A') : '—';
    }
}
