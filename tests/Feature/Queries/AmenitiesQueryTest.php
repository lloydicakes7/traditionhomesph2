<?php

namespace Tests\Feature\Queries;

use App\Enums\BookingStatus;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\User;
use App\Queries\Amenities\AmenityBookingListQuery;
use App\Queries\Amenities\AmenityListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AmenitiesQueryTest extends TestCase
{
    use RefreshDatabase;

    // ===== Facilities =====

    public function test_the_facility_list_searches_pages_and_offers_its_options(): void
    {
        $this->amenity('Clubhouse', true);
        $this->amenity('Basketball Court', true);
        $this->amenity('Storage room', false);

        $query = app(AmenityListQuery::class);

        $this->assertSame(3, $query->paginate()->total());
        $this->assertSame(1, $query->paginate('club')->total());
        $this->assertSame(['Basketball Court', 'Clubhouse', 'Storage room'], array_values($query->options()));
        $this->assertSame(['Basketball Court', 'Clubhouse'], $query->bookable()->pluck('name')->all());
        $this->assertCount(7, $query->bookable()->first()->schedules);
    }

    public function test_the_facility_detail_reads_its_week_and_booking_count(): void
    {
        $amenity = $this->amenity('Clubhouse', true);
        $this->booking($amenity, BookingStatus::PENDING);

        $query = app(AmenityListQuery::class);

        $this->assertSame(7, $query->detail($amenity->id)->schedules->count());
        $this->assertSame(1, $query->bookingCount($amenity->id));
    }

    // ===== Reservations =====

    public function test_the_booking_list_filters_sorts_and_selects(): void
    {
        $clubhouse = $this->amenity('Clubhouse', true);
        $court = $this->amenity('Court', true);

        $this->booking($clubhouse, BookingStatus::PENDING, '09:00');
        $this->booking($court, BookingStatus::APPROVED, '13:00');

        $query = app(AmenityBookingListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['filters' => ['status' => BookingStatus::APPROVED]])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['amenity' => $court->id]])->total());

        $latest = $query->paginate(['sort' => 'starts_at', 'direction' => 'desc'])->items()[0];
        $this->assertSame($court->id, (int) $latest->amenity_id);

        $byName = $query->paginate(['sort' => 'amenity', 'direction' => 'asc'])->items()[0];
        $this->assertSame('Clubhouse', $byName->amenity->name);

        $this->assertCount(2, $query->ids());
        $this->assertCount(1, $query->ids(['filters' => ['status' => BookingStatus::PENDING]]));
    }

    public function test_the_booking_list_stays_inside_its_query_budget(): void
    {
        $amenity = $this->amenity('Clubhouse', true);

        foreach (range(1, 5) as $i) {
            $this->booking($amenity, BookingStatus::PENDING, '0' . $i . ':00');
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        app(AmenityBookingListQuery::class)->paginate();

        $this->assertLessThanOrEqual(10, count(DB::getQueryLog()));
    }

    public function test_one_accounts_reservations_are_read_for_the_portal(): void
    {
        $amenity = $this->amenity('Clubhouse', true);
        $mine = factory(User::class)->create();
        $theirs = factory(User::class)->create();

        $this->booking($amenity, BookingStatus::PENDING, '09:00', $mine);
        $this->booking($amenity, BookingStatus::PENDING, '13:00', $theirs);

        $this->assertSame(1, app(AmenityBookingListQuery::class)->forUser($mine->id)->count());
    }

    public function test_a_month_is_laid_out_as_monday_first_weeks(): void
    {
        $amenity = $this->amenity('Clubhouse', true);
        $this->booking($amenity, BookingStatus::APPROVED, '09:00', null, '2026-10-14');

        $calendar = app(AmenityBookingListQuery::class)->calendar('2026-10');

        $this->assertSame('2026-10', $calendar['month']->format('Y-m'));
        $this->assertSame('2026-09-28', $calendar['weeks'][0][0]->toDateString());
        $this->assertTrue($calendar['byDay']->has('2026-10-14'));
    }

    public function test_the_calendar_shows_only_the_reservations_holding_a_window(): void
    {
        $amenity = $this->amenity('Clubhouse', true);
        $this->booking($amenity, BookingStatus::PENDING, '09:00', null, '2026-10-14');
        $this->booking($amenity, BookingStatus::CANCELLED, '11:00', null, '2026-10-14');
        $this->booking($amenity, BookingStatus::APPROVED, '13:00', null, '2026-10-14');
        $this->booking($amenity, BookingStatus::COMPLETED, '15:00', null, '2026-10-14');

        $calendar = app(AmenityBookingListQuery::class)->calendar('2026-10');
        $statuses = $calendar['byDay']->get('2026-10-14')->pluck('status')->all();

        $this->assertEqualsCanonicalizing(
            [BookingStatus::APPROVED, BookingStatus::COMPLETED],
            $statuses
        );
    }

    // ===== Helpers =====

    private function amenity(string $name, bool $bookable): Amenity
    {
        $amenity = Amenity::create([
            'name'        => $name,
            'rate_type'   => 'fixed',
            'rate'        => 500,
            'is_bookable' => $bookable,
        ]);

        foreach (range(0, 6) as $day) {
            $amenity->schedules()->create([
                'day_of_week' => $day,
                'opens_at'    => '08:00',
                'closes_at'   => '17:00',
            ]);
        }

        return $amenity;
    }

    private function booking(
        Amenity $amenity,
        string $status,
        string $time = '09:00',
        ?User $user = null,
        ?string $date = null
    ): AmenityBooking {
        $day = $date ? \Illuminate\Support\Carbon::parse($date) : now()->addWeek();

        return AmenityBooking::create([
            'amenity_id'     => $amenity->id,
            'user_id'        => $user ? $user->id : null,
            'guest_name'     => $user ? null : 'Walk-in guest',
            'starts_at'      => $day->copy()->setTime((int) substr($time, 0, 2), 0),
            'ends_at'        => $day->copy()->setTime((int) substr($time, 0, 2) + 1, 0),
            'deposit_amount' => 0,
            'deposit_status' => 'none',
            'status'         => $status,
        ]);
    }
}
