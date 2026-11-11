<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The portal's transaction slip, and the portal's payment and document
 * downloads that no longer exist.
 *
 * A homeowner files a request or a booking, the office approves it, and the
 * homeowner prints the slip and brings it to the office to pay and collect.
 * The slip is therefore available once the record is approved, and is guarded
 * by ownership and that approval.
 */
class PortalTransactionSlipTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Requests
    // =====================================================================

    public function test_a_homeowner_downloads_the_slip_of_their_own_request(): void
    {
        $request = $this->request();
        $owner = $request->resident->user;

        $response = $this->actingAs($owner, 'homeowner')
            ->get(route('portal.requests.slip', $request->id));

        $response->assertOk();
        $this->assertStringContainsString('pdf', strtolower((string) $response->headers->get('content-type')));
    }

    public function test_another_homeowners_request_slip_is_denied(): void
    {
        $request = $this->request();

        $this->actingAs(factory(User::class)->create(), 'homeowner')
            ->get(route('portal.requests.slip', $request->id))
            ->assertForbidden();
    }

    public function test_a_pending_requests_slip_is_denied(): void
    {
        $request = $this->request(RequestStatus::PENDING);

        $this->actingAs($request->resident->user, 'homeowner')
            ->get(route('portal.requests.slip', $request->id))
            ->assertForbidden();
    }

    public function test_a_request_is_numbered_when_it_is_filed(): void
    {
        $request = $this->request();

        $this->assertSame(1, preg_match('/^REQ-\d{4}-\d{6}$/', $request->reference));
    }

    public function test_the_slip_prints_the_filing_facts_and_its_note(): void
    {
        $request = $this->request();

        $html = view('pdf.slip', [
            'slip'       => app(\App\Queries\Support\TransactionSlipQuery::class)->build($request),
            'letterhead' => app(\App\Queries\Settings\PrintSettingsQuery::class)->letterhead(),
            'styles'     => '',
            'printedAt'  => now(),
        ])->render();

        $this->assertStringContainsString($request->reference, $html);
        $this->assertStringContainsString('TRANSACTION SLIP', $html);
        $this->assertStringContainsString(__('slips.note'), $html);
        $this->assertStringNotContainsString($request->resident->philsys_card_no ?? '__none__', $html);
    }

    // =====================================================================
    // Bookings
    // =====================================================================

    public function test_a_homeowner_downloads_the_slip_of_their_own_booking(): void
    {
        $booking = $this->booking();
        $owner = $booking->user;

        $response = $this->actingAs($owner, 'homeowner')
            ->get(route('portal.bookings.slip', $booking->id));

        $response->assertOk();
        $this->assertStringContainsString('pdf', strtolower((string) $response->headers->get('content-type')));
        $this->assertSame(1, preg_match('/^BKG-\d{4}-\d{6}$/', (string) $booking->fresh()->reference));
    }

    public function test_another_homeowners_booking_slip_is_denied(): void
    {
        $booking = $this->booking();

        $this->actingAs(factory(User::class)->create(), 'homeowner')
            ->get(route('portal.bookings.slip', $booking->id))
            ->assertForbidden();
    }

    public function test_a_pending_bookings_slip_is_denied(): void
    {
        $booking = $this->booking(BookingStatus::PENDING);

        $this->actingAs($booking->user, 'homeowner')
            ->get(route('portal.bookings.slip', $booking->id))
            ->assertForbidden();
    }

    // =====================================================================
    // Removed flows
    // =====================================================================

    public function test_the_portal_has_no_payment_receipt_or_document_download_routes(): void
    {
        $this->assertFalse(Route::has('portal.payments.receipt.show'));
        $this->assertFalse(Route::has('portal.payments.receipt.download'));
        $this->assertFalse(Route::has('portal.requests.document'));
    }

    public function test_the_statement_of_account_offers_no_way_to_pay(): void
    {
        $owner = factory(User::class)->create();

        $response = $this->actingAs($owner, 'homeowner')->get(route('portal.statement-of-account'));

        $response->assertOk();
        $response->assertDontSee('Pay this bill');
        $response->assertDontSee('paymentModal');
    }

    public function test_the_requests_page_offers_the_slip_once_the_request_is_approved(): void
    {
        $request = $this->request();
        $owner = $request->resident->user;

        $response = $this->actingAs($owner, 'homeowner')->get(route('portal.requests'));

        $response->assertOk();
        $response->assertSee(route('portal.requests.slip', $request->id), false);
        $response->assertDontSee('Download Document');

        $request->update(['status' => RequestStatus::PENDING]);

        $this->actingAs($owner, 'homeowner')
            ->get(route('portal.requests'))
            ->assertOk()
            ->assertDontSee(route('portal.requests.slip', $request->id), false);
    }

    public function test_the_bookings_page_offers_the_slip_once_the_booking_is_approved(): void
    {
        $booking = $this->booking();

        $this->actingAs($booking->user, 'homeowner')
            ->get(route('portal.bookings'))
            ->assertOk()
            ->assertSee(route('portal.bookings.slip', $booking->id), false);

        $booking->update(['status' => BookingStatus::PENDING]);

        $this->actingAs($booking->user, 'homeowner')
            ->get(route('portal.bookings'))
            ->assertOk()
            ->assertDontSee(route('portal.bookings.slip', $booking->id), false);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function request(?string $status = null): RequestModel
    {
        $owner = factory(User::class)->create();

        return RequestModel::create([
            'resident_id' => $owner->resident_id,
            'type'        => RequestType::CERTIFICATION,
            'purpose'     => 'residency',
            'status'      => $status ?: RequestStatus::APPROVED,
        ]);
    }

    private function booking(?string $status = null): AmenityBooking
    {
        $owner = factory(User::class)->create();

        $amenity = Amenity::create([
            'name'      => 'Clubhouse',
            'rate_type' => 'fixed',
            'rate'      => 500,
        ]);

        return AmenityBooking::create([
            'amenity_id' => $amenity->id,
            'user_id'    => $owner->id,
            'starts_at'  => now()->addDay()->setTime(9, 0),
            'ends_at'    => now()->addDay()->setTime(12, 0),
            'status'     => $status ?: BookingStatus::APPROVED,
        ]);
    }
}
