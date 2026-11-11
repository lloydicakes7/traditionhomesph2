<?php

namespace Tests\Feature\Actions\Collections;

use App\Actions\Collections\Payments\ApprovePayment;
use App\Actions\Collections\Payments\DeletePayments;
use App\Actions\Collections\Payments\RecordPayment;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentsActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Record =====

    public function test_recording_a_payment_writes_the_receipt_and_the_activity_log(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1500);

        $payment = app(RecordPayment::class)->handle($bill, [
            'amount'  => 500,
            'paid_at' => now(),
        ], $admin);

        $this->assertSame(PaymentStatus::RECORDED, $payment->status);
        $this->assertNull($payment->journal_entry_id);
        $this->assertSame($admin->id, (int) $payment->received_by_admin_id);
        $this->assertSame(1, preg_match('/^OR-\d{6}$/', $payment->receipt_no));
        $this->assertSame(1000.0, (float) $bill->fresh()->balance);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'received',
            'module'   => 'payment',
        ]);
    }

    public function test_two_receipts_are_numbered_one_after_the_other(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1500);

        $first = app(RecordPayment::class)->handle($bill, ['amount' => 500, 'paid_at' => now()], $admin);
        $second = app(RecordPayment::class)->handle($bill, ['amount' => 500, 'paid_at' => now()], $admin);

        $this->assertNotSame($first->receipt_no, $second->receipt_no);
        $this->assertSame('OR-000001', $first->receipt_no);
        $this->assertSame('OR-000002', $second->receipt_no);
    }

    public function test_a_bill_with_a_waiting_receipt_is_refused(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1500);

        Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $bill->user_id,
            'amount'  => 1500,
            'paid_at' => now(),
            'status'  => PaymentStatus::PENDING,
        ]);

        $this->expectException(ValidationException::class);

        app(RecordPayment::class)->handle($bill, [
            'amount'  => 1500,
            'paid_at' => now(),
        ], $admin);
    }

    // ===== Approve =====

    public function test_approving_a_portal_payment_records_it(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1500);

        $payment = Payment::create([
            'bill_id' => $bill->id,
            'user_id' => $bill->user_id,
            'amount'  => 1500,
            'paid_at' => now(),
            'status'  => PaymentStatus::PENDING,
        ]);

        $this->assertSame(1500.0, (float) $bill->fresh()->balance);

        $approved = app(ApprovePayment::class)->handle($payment, $admin);

        $this->assertSame(PaymentStatus::RECORDED, $approved->status);
        $this->assertSame(1, preg_match('/^OR-\d{6}$/', $approved->receipt_no));
        $this->assertNull($approved->journal_entry_id);
        $this->assertSame(0.0, (float) $bill->fresh()->balance);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'received',
            'module'   => 'payment',
        ]);
    }

    // ===== Delete =====

    public function test_deleting_a_payment_removes_the_receipt_and_reopens_the_bill(): void
    {
        $admin = $this->admin();
        $bill = $this->openBill(factory(User::class)->create(), 1500);

        $payment = app(RecordPayment::class)->handle($bill, [
            'amount'  => 1500,
            'paid_at' => now(),
        ], $admin);

        $this->assertSame(0.0, (float) $bill->fresh()->balance);

        $deleted = app(DeletePayments::class)->handle([$payment->id], $admin);

        $this->assertSame(1, $deleted);
        $this->assertNull(Payment::find($payment->id));
        $this->assertSame(1500.0, (float) $bill->fresh()->balance);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'payment',
        ]);
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'payments-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
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
