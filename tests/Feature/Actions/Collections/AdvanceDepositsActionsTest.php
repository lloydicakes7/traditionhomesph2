<?php

namespace Tests\Feature\Actions\Collections;

use App\Actions\Collections\AdvanceDeposits\ApplyAdvanceDeposit;
use App\Actions\Collections\AdvanceDeposits\DeleteAdvanceDeposits;
use App\Actions\Collections\AdvanceDeposits\RecordAdvanceDeposit;
use App\Actions\Collections\AdvanceDeposits\RefundAdvanceDeposit;
use App\Enums\AdvanceDepositStatus;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdvanceDepositsActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Record =====

    public function test_recording_a_deposit_holds_the_amount_received_as_credit(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();

        $deposit = app(RecordAdvanceDeposit::class)->handle([
            'user_id'      => $user->id,
            'amount'       => 1000,
            'deposited_at' => '2026-06-01',
        ], $admin);

        $this->assertSame(1000.0, (float) $deposit->amount);
        $this->assertSame(1000.0, (float) $deposit->balance);
        $this->assertSame(AdvanceDepositStatus::AVAILABLE, $deposit->status);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'advance_deposit',
        ]);
    }

    // ===== Apply =====

    public function test_applying_a_deposit_settles_the_bill_from_credit(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1200);

        $deposit = AdvanceDeposit::create([
            'user_id'      => $bill->user_id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subMonth(),
        ]);

        $payment = app(ApplyAdvanceDeposit::class)->handle($deposit, $bill, '500.00', $admin);

        $this->assertSame(500.0, (float) $payment->amount);
        $this->assertSame($deposit->id, (int) $payment->advance_deposit_id);
        $this->assertSame(700.0, (float) $bill->fresh()->balance);
        $this->assertSame(0.0, (float) $deposit->fresh()->balance);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'received',
            'module'   => 'payment',
        ]);
    }

    public function test_applying_part_of_the_credit_leaves_the_rest_held(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1200);

        $deposit = AdvanceDeposit::create([
            'user_id'      => $bill->user_id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subMonth(),
        ]);

        $payment = app(ApplyAdvanceDeposit::class)->handle($deposit, $bill, '200.00', $admin);

        $this->assertSame(200.0, (float) $payment->amount);
        $this->assertSame(1000.0, (float) $bill->fresh()->balance);
        $this->assertSame(300.0, (float) $deposit->fresh()->balance);
    }

    public function test_an_amount_over_the_credit_draws_nothing(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1200);

        $deposit = AdvanceDeposit::create([
            'user_id'      => $bill->user_id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subMonth(),
        ]);

        $this->assertNull(app(ApplyAdvanceDeposit::class)->handle($deposit, $bill, '500.01', $admin));
        $this->assertSame(500.0, (float) $deposit->fresh()->balance);
        $this->assertSame(1200.0, (float) $bill->fresh()->balance);
    }

    public function test_a_settled_bill_draws_nothing(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1200);

        $deposit = AdvanceDeposit::create([
            'user_id'      => $bill->user_id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now(),
        ]);

        $bill->update(['balance' => 0]);

        $this->assertNull(app(ApplyAdvanceDeposit::class)->handle($deposit, $bill->fresh(), '500.00', $admin));
        $this->assertSame(500.0, (float) $deposit->fresh()->balance);
    }

    // ===== Refund =====

    public function test_refunding_a_deposit_returns_what_is_left(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();

        $deposit = AdvanceDeposit::create([
            'user_id'      => $user->id,
            'amount'       => 800,
            'balance'      => 300,
            'status'       => AdvanceDepositStatus::PARTIALLY_USED,
            'deposited_at' => now()->subMonth(),
        ]);

        $refunded = app(RefundAdvanceDeposit::class)->handle($deposit, $admin);

        $this->assertSame(0.0, (float) $refunded->balance);
        $this->assertSame(AdvanceDepositStatus::REFUNDED, $refunded->status);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'refunded',
            'module'   => 'advance_deposit',
        ]);
    }

    // ===== Delete =====

    public function test_deleting_a_deposit_leaves_a_used_one_alone(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();
        $bill = $this->openBill($user, 500);

        $free = AdvanceDeposit::create([
            'user_id'      => $user->id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now(),
        ]);

        $used = AdvanceDeposit::create([
            'user_id'      => $user->id,
            'amount'       => 500,
            'balance'      => 0,
            'status'       => AdvanceDepositStatus::USED,
            'deposited_at' => now(),
        ]);

        Payment::create([
            'bill_id'            => $bill->id,
            'advance_deposit_id' => $used->id,
            'user_id'            => $user->id,
            'amount'             => 500,
            'paid_at'            => now(),
        ]);

        $deleted = app(DeleteAdvanceDeposits::class)->handle([$free->id, $used->id], $admin);

        $this->assertSame(1, $deleted);
        $this->assertNull(AdvanceDeposit::find($free->id));
        $this->assertNotNull(AdvanceDeposit::find($used->id));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'advance_deposit',
        ]);
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'deposit-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    private function openBill(User $user, float $total): Bill
    {
        $bill = Bill::create([
            'reference'      => 'INV-' . uniqid(),
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
            'name'       => 'Charge',
            'quantity'   => 1,
            'unit_price' => $total,
            'amount'     => $total,
        ]);

        return $bill;
    }
}
