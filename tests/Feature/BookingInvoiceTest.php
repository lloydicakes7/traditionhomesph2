<?php

namespace Tests\Feature;

use App\Actions\Amenities\Bookings\CreateBookingInvoice;
use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\BookingTableList;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingFormModal;
use App\Http\Livewire\Admin\Collections\Invoices\Modals\InvoiceFormModal;
use App\Http\Livewire\Admin\Collections\Modals\RecordPaymentModal;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Models\Role;
use App\Utility\MoneyHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The booking form and the shared Collections invoice form the officer raises
 * a reservation's bill through.
 *
 * Two rules are pinned down: the form switches between a homeowner account and
 * a guest and previews the rate for the window plus the refundable deposit
 * typed on the reservation; and a booking never carries a bill until the
 * officer asks for one, prefilled from the reservation on the invoices form.
 */
class BookingInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'booking-invoice-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function amenity(float $rate = 1500): Amenity
    {
        return Amenity::create([
            'name'        => 'Clubhouse',
            'rate_type'   => 'fixed',
            'rate'        => $rate,
            'is_bookable' => true,
        ]);
    }

    public function test_the_shared_invoice_dialog_opens_prefilled_and_raises_the_bill(): void
    {
        $this->signIn();

        $amenity = $this->amenity();

        $booking = AmenityBooking::create([
            'amenity_id'     => $amenity->id,
            'guest_name'     => 'Walk-in guest',
            'starts_at'      => now()->addWeek()->setTime(8, 0),
            'ends_at'        => now()->addWeek()->setTime(12, 0),
            'deposit_amount' => 500,
            'deposit_status' => DepositStatus::HELD,
            'status'         => BookingStatus::APPROVED,
            'approved_at'    => now(),
        ]);

        Livewire::test(InvoiceFormModal::class)
            ->assertSet('loaded', false)
            ->emit('openBookingInvoiceFormModal', $booking->id)
            ->assertSet('loaded', true)
            ->assertSet('bookingId', $booking->id)
            ->assertSet('isGuest', true)
            ->assertSet('form.billed_to_name', 'Walk-in guest')
            ->assertSet('items.0.name', 'Amenity booking — Clubhouse')
            ->assertSet('items.1.name', 'Deposit — Clubhouse')
            ->call('confirm')
            ->assertHasNoErrors()
            ->assertEmitted('invoice-saved');

        $booking->refresh();

        $this->assertNotNull($booking->bill_id);
        $this->assertSame('amenity_booking', $booking->bill->type);
        $this->assertSame('Walk-in guest', $booking->bill->billed_to_name);
        $this->assertSame(2000.0, (float) $booking->bill->total);
        $this->assertTrue(Bill::query()->invoices()->whereKey($booking->bill_id)->exists());
    }

    public function test_an_amenity_invoice_opens_and_edits_on_the_shared_form(): void
    {
        $admin = $this->signIn();

        $booking = AmenityBooking::create([
            'amenity_id'     => $this->amenity()->id,
            'guest_name'     => 'Walk-in guest',
            'starts_at'      => now()->addWeek()->setTime(8, 0),
            'ends_at'        => now()->addWeek()->setTime(12, 0),
            'deposit_amount' => 500,
            'deposit_status' => DepositStatus::HELD,
            'status'         => BookingStatus::APPROVED,
            'approved_at'    => now(),
        ]);

        $bill = app(CreateBookingInvoice::class)->handle($booking, [
            'bill_date' => now()->toDateString(),
            'due_date'  => now()->addWeek()->toDateString(),
        ], $admin);

        Livewire::test(InvoiceFormModal::class)
            ->call('openEdit', $bill->id)
            ->assertSet('bookingId', $booking->id)
            ->assertSet('isGuest', true)
            ->set('form.billed_to_name', 'Renamed guest')
            ->call('confirm')
            ->assertHasNoErrors();

        $this->assertSame('Renamed guest', $bill->fresh()->billed_to_name);
        $this->assertSame('Renamed guest', $booking->fresh()->guest_name);
    }

    public function test_the_shared_payment_dialog_opens_for_an_amenity_invoice(): void
    {
        $admin = $this->signIn();

        $booking = AmenityBooking::create([
            'amenity_id'     => $this->amenity()->id,
            'guest_name'     => 'Walk-in guest',
            'starts_at'      => now()->addWeek()->setTime(8, 0),
            'ends_at'        => now()->addWeek()->setTime(12, 0),
            'deposit_amount' => 500,
            'deposit_status' => DepositStatus::HELD,
            'status'         => BookingStatus::APPROVED,
            'approved_at'    => now(),
        ]);

        $bill = app(CreateBookingInvoice::class)->handle($booking, [
            'bill_date' => now()->toDateString(),
            'due_date'  => now()->addWeek()->toDateString(),
        ], $admin);

        Livewire::test(RecordPaymentModal::class)
            ->call('load', (string) $bill->id)
            ->assertSet('loaded', true)
            ->assertSet('bill', (string) $bill->id)
            ->assertDispatchedBrowserEvent('modal-open');
    }

    public function test_a_booking_row_opens_the_shared_invoice_form(): void
    {
        $this->signIn();

        $booking = AmenityBooking::create([
            'amenity_id'     => $this->amenity()->id,
            'guest_name'     => 'Walk-in guest',
            'starts_at'      => now()->addWeek()->setTime(8, 0),
            'ends_at'        => now()->addWeek()->setTime(12, 0),
            'deposit_amount' => 500,
            'deposit_status' => DepositStatus::HELD,
            'status'         => BookingStatus::APPROVED,
            'approved_at'    => now(),
        ]);

        Livewire::test(BookingTableList::class)
            ->call('createInvoice', $booking->id)
            ->assertEmitted('openBookingInvoiceFormModal', $booking->id)
            ->assertDispatchedBrowserEvent('modal-open');
    }

    public function test_the_bookings_table_has_five_columns(): void
    {
        $this->signIn();

        $html = $this->get(route('amenities.bookings'))->assertOk()->getContent();

        preg_match('/<table[^>]*id="amenityBookingsTable"[^>]*>(.*?)<\/table>/s', $html, $table);
        preg_match_all('/<th\b/', $table[1] ?? '', $columns);

        $this->assertCount(5, $columns[0]);
    }

    public function test_the_booking_form_switches_booker_and_previews_amount_and_deposit(): void
    {
        $this->signIn();

        $amenity = $this->amenity();

        Livewire::test(BookingFormModal::class)
            ->call('openCreate')
            ->assertSet('isGuest', false)
            ->assertSee('name="form.user_id"', false)
            ->set('form.amenity_id', $amenity->id)
            ->set('form.start_time', '08:00')
            ->set('form.end_time', '12:00')
            ->set('form.deposit_amount', 300)
            ->assertSee('Amount to pay')
            ->assertSee(MoneyHelper::format(1500))
            ->assertSee(MoneyHelper::format(300))
            ->set('isGuest', true)
            ->assertSet('form.booker_type', 'guest')
            ->assertSee('name="form.guest_name"', false)
            ->assertDontSee('name="form.user_id"', false);
    }
}
