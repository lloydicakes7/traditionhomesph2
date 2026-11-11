<?php

namespace Tests\Feature\Actions\Amenities;

use App\Actions\Amenities\Bookings\ApproveBooking;
use App\Actions\Amenities\Bookings\BookForAdmin;
use App\Actions\Amenities\Bookings\CancelBooking;
use App\Actions\Amenities\Bookings\CompleteBooking;
use App\Actions\Amenities\Bookings\CreateBookingInvoice;
use App\Actions\Amenities\Bookings\DeleteBookings;
use App\Actions\Amenities\Bookings\ForfeitDeposit;
use App\Actions\Amenities\Bookings\RefundDeposit;
use App\Actions\Amenities\Bookings\RequestBooking;
use App\Actions\Amenities\Bookings\UpdateBooking;
use App\Actions\Amenities\CreateAmenity;
use App\Actions\Amenities\DeleteAmenities;
use App\Actions\Amenities\UpdateAmenity;
use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AmenityActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Facilities =====

    public function test_creating_a_facility_saves_its_week_and_logs(): void
    {
        $admin = $this->admin();

        $amenity = app(CreateAmenity::class)->handle($this->payload(), $this->week(6), null, $admin);

        $this->assertSame('Clubhouse', $amenity->name);
        $this->assertSame(1500.0, (float) $amenity->rate);
        $this->assertSame(7, $amenity->schedules()->count());
        $this->assertSame(0, $amenity->schedules()->whereNotNull('opens_at')->where('day_of_week', 6)->count());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'amenity',
        ]);
    }

    public function test_updating_a_facility_rewrites_the_week_and_logs(): void
    {
        $admin = $this->admin();
        $amenity = app(CreateAmenity::class)->handle($this->payload(), $this->week(), null, $admin);

        app(UpdateAmenity::class)->handle(
            $amenity,
            ['name' => 'Pool', 'rate_type' => 'hourly', 'rate' => 250, 'is_bookable' => true],
            $this->week(0),
            null,
            false,
            $admin
        );

        $this->assertSame('Pool', $amenity->name);
        $this->assertSame(0, $amenity->schedules()->where('day_of_week', 0)->whereNotNull('opens_at')->count());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'updated',
            'module'   => 'amenity',
        ]);
    }

    public function test_a_facility_that_has_been_booked_is_not_deleted(): void
    {
        $admin = $this->admin();
        $booked = $this->amenity('Clubhouse');
        $free = $this->amenity('Court');

        $this->booking($booked, BookingStatus::PENDING);

        $removed = app(DeleteAmenities::class)->handle([$booked->id, $free->id], $admin);

        $this->assertSame(1, $removed);
        $this->assertNotNull(Amenity::find($booked->id));
        $this->assertNull(Amenity::find($free->id));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'amenity',
        ]);
    }

    // ===== Taking a reservation =====

    public function test_the_office_books_a_facility_without_raising_a_bill_behind_its_back(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Clubhouse', 1500);

        $booking = app(BookForAdmin::class)->handle(
            $this->bookingPayload($amenity, ['guest_name' => 'Walk-in guest', 'deposit_amount' => 500]),
            $admin
        );

        $this->assertSame(BookingStatus::APPROVED, $booking->status);
        $this->assertNotNull($booking->approved_at);
        $this->assertNull($booking->bill_id);
        $this->assertTrue($booking->can_be_invoiced);
        $this->assertSame(500.0, (float) $booking->deposit_amount);
        $this->assertSame(DepositStatus::HELD, $booking->deposit_status);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'amenity_booking',
        ]);
    }

    public function test_the_office_raises_the_invoice_the_reservation_is_paid_through(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Clubhouse', 1500);

        $booking = app(BookForAdmin::class)->handle(
            $this->bookingPayload($amenity, ['guest_name' => 'Walk-in guest', 'deposit_amount' => 500]),
            $admin
        );

        $bill = app(CreateBookingInvoice::class)->handle($booking, [
            'bill_date' => now()->toDateString(),
            'due_date'  => now()->addWeek()->toDateString(),
            'notes'     => 'Clubhouse reservation',
        ], $admin);

        $booking->refresh();

        $this->assertSame((int) $bill->id, (int) $booking->bill_id);
        $this->assertSame(2000.0, (float) $booking->bill->total);
        $this->assertSame(1500.0, $booking->rate_amount);
        $this->assertSame(DepositStatus::HELD, $booking->deposit_status);
        $this->assertTrue($booking->can_be_paid);
        $this->assertFalse($booking->can_be_completed);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'invoiced',
            'module'   => 'amenity_booking',
        ]);
    }

    public function test_editing_an_unpaid_booking_reprices_its_invoice(): void
    {
        $admin = $this->admin();
        $amenity = Amenity::create([
            'name'        => 'Pool',
            'rate_type'   => 'hourly',
            'rate'        => 250,
            'is_bookable' => true,
        ]);

        $booking = $this->booking($amenity, BookingStatus::APPROVED, '09:00', '12:00', null, 500);
        $bill = app(CreateBookingInvoice::class)->handle($booking, [
            'bill_date' => now()->toDateString(),
            'due_date'  => now()->addWeek()->toDateString(),
        ], $admin);

        $this->assertSame(1250.0, (float) $bill->total);

        app(UpdateBooking::class)->handle($booking, $this->bookingPayload($amenity, [
            'start_time'     => '13:00',
            'end_time'       => '15:00',
            'deposit_amount' => 500,
        ]), $admin);

        $bill->refresh();

        $this->assertSame(1000.0, (float) $bill->total);
        $this->assertSame(500.0, $booking->fresh()->rate_amount);
    }

    public function test_updating_a_booking_moves_its_window(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Clubhouse');
        $booking = $this->booking($amenity, BookingStatus::PENDING, '09:00', '10:00');

        app(UpdateBooking::class)->handle($booking, $this->bookingPayload($amenity, [
            'start_time' => '13:00',
            'end_time'   => '15:00',
        ]), $admin);

        $this->assertSame('13:00', $booking->starts_at->format('H:i'));
        $this->assertSame('15:00', $booking->ends_at->format('H:i'));
    }

    public function test_a_homeowners_request_waits_for_the_office(): void
    {
        $this->admin();
        $amenity = $this->amenity('Clubhouse');
        $user = factory(User::class)->create();

        $booking = app(RequestBooking::class)->handle($amenity, $this->requestPayload(), $user);

        $this->assertSame(BookingStatus::PENDING, $booking->status);
        $this->assertSame($user->id, $booking->user_id);
        $this->assertNull($booking->bill_id);
    }

    // ===== Moving a reservation along =====

    public function test_approving_a_request_holds_the_window_and_tells_the_homeowner(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Clubhouse', 1500);
        $user = factory(User::class)->create();
        $booking = $this->booking($amenity, BookingStatus::PENDING, '09:00', '12:00', $user);

        app(ApproveBooking::class)->handle($booking, $admin);

        $booking->refresh();

        $this->assertSame(BookingStatus::APPROVED, $booking->status);
        $this->assertNull($booking->bill_id);
        $this->assertTrue($booking->can_be_invoiced);
        $this->assertTrue(DB::table('notifications')->where('notifiable_id', $user->id)->exists());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'approved',
            'module'   => 'amenity_booking',
        ]);
    }

    public function test_a_payment_recorded_on_the_ledger_settles_the_reservation(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Clubhouse', 1500);
        $user = factory(User::class)->create();
        $booking = $this->booking($amenity, BookingStatus::APPROVED, '09:00', '12:00', $user);

        app(CreateBookingInvoice::class)->handle($booking, [
            'bill_date' => now()->toDateString(),
            'due_date'  => now()->addWeek()->toDateString(),
            'notes'     => 'Clubhouse reservation',
        ], $admin);

        $booking->refresh()->bill->settle([
            'amount'    => 1500,
            'paid_at'   => now(),
            'reference_no' => 'OR-1',
        ]);

        $booking->refresh();

        // Payment is a fact about the bill, not a status of its own.
        $this->assertSame(BookingStatus::APPROVED, $booking->status);
        $this->assertFalse($booking->can_be_paid);
        $this->assertTrue($booking->can_be_completed);
        $this->assertSame(0.0, (float) $booking->bill->balance);
        $this->assertSame(1, $booking->bill->payments()->count());
    }

    public function test_completing_cancelling_and_deleting_a_booking(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Court');

        $completed = $this->booking($amenity, BookingStatus::APPROVED);
        app(CompleteBooking::class)->handle($completed, $admin);
        $this->assertSame(BookingStatus::COMPLETED, $completed->fresh()->status);

        $cancelled = $this->booking($amenity, BookingStatus::PENDING);
        app(CancelBooking::class)->handle($cancelled, 'Double booked', $admin);
        $cancelled->refresh();
        $this->assertSame(BookingStatus::CANCELLED, $cancelled->status);
        $this->assertSame('Double booked', $cancelled->remarks);

        $deleted = $this->booking($amenity, BookingStatus::PENDING);
        $this->assertSame(1, app(DeleteBookings::class)->handle([$deleted->id], $admin));
        $this->assertNull(AmenityBooking::find($deleted->id));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'amenity_booking',
        ]);
    }

    public function test_the_deposit_is_returned_or_kept(): void
    {
        $admin = $this->admin();
        $amenity = $this->amenity('Clubhouse', 1500);

        $refunded = $this->booking($amenity, BookingStatus::COMPLETED, '09:00', '12:00', null, 500);
        app(RefundDeposit::class)->handle($refunded, $admin);
        $refunded->refresh();
        $this->assertSame(DepositStatus::REFUNDED, $refunded->deposit_status);
        $this->assertNotNull($refunded->deposit_settled_at);

        $forfeited = $this->booking($amenity, BookingStatus::COMPLETED, '13:00', '15:00', null, 500);
        app(ForfeitDeposit::class)->handle($forfeited, $admin);
        $this->assertSame(DepositStatus::FORFEITED, $forfeited->fresh()->deposit_status);
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'amenity-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    private function amenity(string $name, float $rate = 500): Amenity
    {
        return Amenity::create([
            'name'        => $name,
            'rate_type'   => 'fixed',
            'rate'        => $rate,
            'is_bookable' => true,
        ]);
    }

    private function booking(
        Amenity $amenity,
        string $status,
        string $from = '09:00',
        string $to = '12:00',
        ?User $user = null,
        float $deposit = 0
    ): AmenityBooking {
        return AmenityBooking::create([
            'amenity_id'     => $amenity->id,
            'user_id'        => $user ? $user->id : null,
            'guest_name'     => $user ? null : 'Walk-in guest',
            'starts_at'      => now()->addWeek()->setTime((int) substr($from, 0, 2), 0),
            'ends_at'        => now()->addWeek()->setTime((int) substr($to, 0, 2), 0),
            'deposit_amount' => $deposit,
            'deposit_status' => $deposit > 0 ? DepositStatus::HELD : DepositStatus::NONE,
            'status'         => $status,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'        => 'Clubhouse',
            'rate_type'   => 'fixed',
            'rate'        => 1500,
            'is_bookable' => true,
            'description' => 'Air-conditioned function hall',
        ], $overrides);
    }

    private function week(?int $closedDay = null): array
    {
        $week = [];

        foreach (range(0, 6) as $day) {
            $closed = $day === $closedDay;

            $week[$day] = [
                'opens_at'  => $closed ? '' : '08:00',
                'closes_at' => $closed ? '' : '17:00',
            ];
        }

        return $week;
    }

    private function bookingPayload(Amenity $amenity, array $overrides = []): array
    {
        return array_merge([
            'amenity_id'     => $amenity->id,
            'user_id'        => '',
            'guest_name'     => 'Walk-in guest',
            'date'           => now()->addWeek()->toDateString(),
            'start_time'     => '13:00',
            'end_time'       => '15:00',
            'deposit_amount' => 0,
            'purpose'        => 'Birthday',
            'remarks'        => '',
        ], $overrides);
    }

    private function requestPayload(): array
    {
        return [
            'date'       => now()->addWeek()->toDateString(),
            'start_time' => '13:00',
            'end_time'   => '15:00',
            'purpose'    => 'Family reunion',
        ];
    }
}
