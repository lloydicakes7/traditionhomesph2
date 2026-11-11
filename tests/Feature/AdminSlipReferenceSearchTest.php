<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Queries\Amenities\AmenityBookingListQuery;
use App\Queries\Support\Requests\RequestListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The office finds a record by the number on the slip the homeowner hands over.
 *
 * Both lists search their own scope, so the reference is one of the terms the
 * existing search box already accepts — no separate lookup screen.
 */
class AdminSlipReferenceSearchTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Requests
    // =====================================================================

    public function test_a_request_is_found_by_its_reference(): void
    {
        $wanted = $this->request();
        $other = $this->request();

        $page = app(RequestListQuery::class)->paginate(['search' => $wanted->reference]);

        $this->assertSame([$wanted->id], $page->pluck('id')->all());
        $this->assertNotContains($other->id, $page->pluck('id')->all());
    }

    // =====================================================================
    // Bookings
    // =====================================================================

    public function test_a_booking_is_found_by_its_reference(): void
    {
        $wanted = $this->booking();
        $other = $this->booking();

        $page = app(AmenityBookingListQuery::class)->paginate(['search' => $wanted->reference]);

        $this->assertSame([$wanted->id], $page->pluck('id')->all());
        $this->assertNotContains($other->id, $page->pluck('id')->all());
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function request(): RequestModel
    {
        $owner = factory(User::class)->create();

        return RequestModel::create([
            'resident_id' => $owner->resident_id,
            'type'        => RequestType::CERTIFICATION,
            'purpose'     => 'residency',
            'status'      => RequestStatus::PENDING,
        ]);
    }

    private function booking(): AmenityBooking
    {
        $amenity = Amenity::create([
            'name'      => 'Clubhouse',
            'rate_type' => 'fixed',
            'rate'      => 500,
        ]);

        return AmenityBooking::create([
            'amenity_id' => $amenity->id,
            'user_id'    => factory(User::class)->create()->id,
            'starts_at'  => now()->addDay()->setTime(9, 0),
            'ends_at'    => now()->addDay()->setTime(12, 0),
            'status'     => 'pending',
        ]);
    }
}
