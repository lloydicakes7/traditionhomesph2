<?php

namespace Tests\Feature;

use App\Actions\Collections\MonthlyDues\ApplyAdvanceDepositsToDues;
use App\Actions\Collections\Payments\RecordPayment;
use App\Enums\AdvanceDepositStatus;
use App\Enums\BillType;
use App\Enums\PaymentStatus;
use App\Http\Livewire\Admin\Collections\Modals\RecordPaymentModal;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Queries\Collections\Payments\BillOptionQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * One bill, one waiting receipt.
 *
 * A payment a homeowner files from the portal is **pending** — it settles
 * nothing on the bill — so the office must not record the same money over the
 * counter while it waits, which would put the receipt on the books twice. The
 * ledger service refuses that, the picker does not offer such a bill, and the
 * Record Payment dialog shows the reason instead of swallowing it.
 *
 * A receipt the office has already recorded is a different matter: it is
 * counted in the bill's balance, so further receipts against what is left are
 * ordinary part payments and stay allowed.
 */
class PaymentDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_bill_with_a_portal_payment_waiting_cannot_be_collected_again(): void
    {
        $homeowner = factory(User::class)->create();
        $bill = $this->openBill($homeowner, 1500, 'INV-2026-0001');
        $admin = $this->signInAdmin();

        $this->pending($bill, $homeowner, 1500, 'GCASH-777');

        $this->expectException(ValidationException::class);

        try {
            app(RecordPayment::class)->handle($bill, [
                'amount'  => 1500,
                'paid_at' => now(),
            ], $admin);
        } finally {
            // Nothing was written, and the bill still stands for its balance.
            $this->assertSame(1, Payment::count());

            $bill->refresh();
            $this->assertSame(0.0, (float) $bill->paid_amount);
            $this->assertSame(1500.0, (float) $bill->balance);
        }
    }

    public function test_the_record_payment_dialog_shows_the_refusal(): void
    {
        $homeowner = factory(User::class)->create();
        $bill = $this->openBill($homeowner, 1500, 'INV-2026-0001');
        $this->signInAdmin();

        $this->pending($bill, $homeowner, 1500, 'GCASH-777');

        Livewire::test(RecordPaymentModal::class)
            ->emit('openRecordPaymentModal')
            ->set('bill', (string) $bill->id)
            ->set('amount', 1500)
            ->call('confirm')
            ->assertHasErrors('bill');

        // The dialog stays open on the refusal, and no second receipt exists.
        $this->assertSame(1, Payment::count());
        $this->assertSame(1, Payment::where('bill_id', $bill->id)->count());
    }

    public function test_the_picker_does_not_offer_a_bill_a_portal_payment_is_waiting_on(): void
    {
        $homeowner = factory(User::class)->create();
        $waiting = $this->openBill($homeowner, 1500, 'INV-2026-0001');
        $free = $this->openBill($homeowner, 800, 'INV-2026-0002');
        $this->signInAdmin();

        $this->pending($waiting, $homeowner, 1500, 'GCASH-777');

        $options = app(BillOptionQuery::class)->options();

        $this->assertArrayNotHasKey((string) $waiting->id, $options);
        $this->assertArrayHasKey((string) $free->id, $options);
    }

    public function test_a_recorded_receipt_leaves_the_rest_of_the_bill_collectable(): void
    {
        $homeowner = factory(User::class)->create();
        $bill = $this->openBill($homeowner, 1500, 'INV-2026-0001');
        $admin = $this->signInAdmin();

        app(RecordPayment::class)->handle($bill, [
            'amount'  => 500,
            'paid_at' => now(),
        ], $admin);

        // What is left is a part payment, not a duplicate: the office takes it.
        app(RecordPayment::class)->handle($bill, [
            'amount'  => 1000,
            'paid_at' => now(),
        ], $admin);

        $bill->refresh();

        $this->assertSame(2, Payment::where('bill_id', $bill->id)->count());
        $this->assertSame(0.0, (float) $bill->balance);
    }

    public function test_the_monthly_dues_sweep_skips_dues_a_portal_payment_is_waiting_on(): void
    {
        $homeowner = factory(User::class)->create();
        $admin = $this->signInAdmin();

        $due = Bill::create([
            'reference'      => 'DUE-2026-09',
            'user_id'        => $homeowner->id,
            'billed_to_name' => $homeowner->name,
            'type'           => BillType::MONTHLY_DUE,
            'billing_period' => '2026-09',
            'bill_date'      => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'subtotal'       => 1200,
            'total'          => 1200,
            'paid_amount'    => 0,
            'balance'        => 1200,
            'status'         => 'unpaid',
        ]);

        AdvanceDeposit::create([
            'user_id'      => $homeowner->id,
            'amount'       => 1200,
            'balance'      => 1200,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subMonth(),
        ]);

        $this->pending($due, $homeowner, 1200, 'GCASH-888');

        // The credit sweep only collects what the office can still collect: a
        // due the portal has already been paid against is left for approval.
        $drawn = app(ApplyAdvanceDepositsToDues::class)->handle('2026-09', $admin);

        $this->assertSame(0, $drawn);
        $this->assertSame(1, Payment::where('bill_id', $due->id)->count());
        $this->assertSame(1200.0, (float) AdvanceDeposit::first()->balance);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'payment-duplicate-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function pending(Bill $bill, User $homeowner, float $amount, string $reference): Payment
    {
        return Payment::create([
            'bill_id'      => $bill->id,
            'user_id'      => $homeowner->id,
            'amount'       => $amount,
            'reference_no' => $reference,
            'paid_at'      => now(),
            'status'       => PaymentStatus::PENDING,
        ]);
    }

    private function openBill(User $user, float $total, string $reference): Bill
    {
        $bill = Bill::create([
            'reference'      => $reference,
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'subtotal'       => $total,
            'total'          => $total,
            'paid_amount'    => 0,
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
