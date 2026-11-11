<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The homeowner dashboard's year-at-a-glance graphs: the amenity bookings a
 * household made and the payments it filed, shown side by side.
 */
class PortalDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_shows_the_payments_graph_alongside_the_bookings_graph(): void
    {
        $homeowner = factory(User::class)->create();
        $bill = $this->bill($homeowner, 1500, 'INV-2026-0200');

        Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $homeowner->id,
            'amount'  => 1500,
            'paid_at' => now(),
            'status'  => PaymentStatus::RECORDED,
        ]);

        $response = $this->actingAs($homeowner, 'homeowner')->get(route('portal.dashboard'));

        $response->assertOk();

        // Both charts are on the page, one beside the other.
        $response->assertSee('My Amenity Bookings');
        $response->assertSee('My Payments');

        // The month's receipt is drawn as money, not as a count.
        $response->assertSee('1,500');
    }

    /**
     * An invoice for a homeowner's account, with the one line a recalculation
     * reads its total back from.
     */
    private function bill(User $account, float $total, string $reference): Bill
    {
        $bill = Bill::create([
            'reference'      => $reference,
            'user_id'        => $account->id,
            'billed_to_name' => $account->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => $total,
            'total'          => $total,
            'balance'        => $total,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Charge ' . $reference,
            'quantity'   => 1,
            'unit_price' => $total,
            'amount'     => $total,
        ]);

        return $bill;
    }
}
