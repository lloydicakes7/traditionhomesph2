<?php

namespace Tests\Feature\Queries;

use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use App\Queries\Collections\Payments\ReceiptQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReceiptQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_the_receipt_of_a_part_payment(): void
    {
        $bill = $this->bill(1500);
        $payment = $this->payment($bill, 500, 'OR-000001');

        $receipt = app(ReceiptQuery::class)->build($payment);

        $this->assertSame('OR-000001', $receipt['receipt_no']);
        $this->assertSame('Juan Cruz', $receipt['payer']);
        $this->assertSame($bill->reference, $receipt['bill_reference']);
        $this->assertSame('₱500.00', $receipt['amount_paid']);
        $this->assertSame('Five Hundred Pesos Only', $receipt['amount_in_words']);
        $this->assertSame('₱1,000.00', $receipt['balance_after']);
        $this->assertSame([['name' => 'Monthly Dues', 'quantity' => '1', 'unit_price' => '₱1,500.00', 'amount' => '₱1,500.00']], $receipt['items']);
    }

    public function test_the_balance_after_a_second_payment_counts_the_earlier_one(): void
    {
        $bill = $this->bill(1500);

        $first = $this->payment($bill, 500, 'OR-000001', now()->subMinute());
        $second = $this->payment($bill, 1000, 'OR-000002');

        $this->assertSame('₱1,000.00', app(ReceiptQuery::class)->build($first)['balance_after']);
        $this->assertSame('₱0.00', app(ReceiptQuery::class)->build($second)['balance_after']);
    }

    public function test_a_payment_drawn_from_an_advance_deposit_names_the_deposit(): void
    {
        $bill = $this->bill(1500);

        $deposit = AdvanceDeposit::create([
            'user_id'      => $bill->user_id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => 'available',
            'deposited_at' => now()->toDateString(),
        ]);

        $payment = Payment::create([
            'bill_id'            => $bill->id,
            'advance_deposit_id' => $deposit->id,
            'user_id'            => $bill->user_id,
            'amount'             => 500,
            'paid_at'            => now(),
            'status'             => 'recorded',
            'receipt_no'         => 'OR-000001',
        ]);

        $this->assertSame(
            'Applied from advance deposit #' . $deposit->id,
            app(ReceiptQuery::class)->build($payment)['method']
        );
    }

    public function test_the_receipt_is_built_without_a_query_per_relation(): void
    {
        $bill = $this->bill(1500);
        $payment = $this->payment($bill, 500, 'OR-000001', now(), $this->admin());

        DB::enableQueryLog();

        app(ReceiptQuery::class)->build($payment);

        $this->assertLessThanOrEqual(10, count(DB::getQueryLog()));
    }

    // ===== Helpers =====

    public function bill(float $total): Bill
    {
        $bill = Bill::create([
            'reference'      => 'INV-2026-0001',
            'user_id'        => factory(User::class)->create()->id,
            'billed_to_name' => 'Juan Cruz',
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'due_date'       => now()->addDays(10)->toDateString(),
            'subtotal'       => $total,
            'total'          => $total,
            'paid_amount'    => 0,
            'balance'       => $total,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Monthly Dues',
            'quantity'   => 1,
            'unit_price' => $total,
            'amount'     => $total,
        ]);

        return $bill;
    }

    public function payment(Bill $bill, float $amount, string $receiptNo, $paidAt = null, ?Admin $by = null): Payment
    {
        return Payment::create([
            'bill_id'              => $bill->id,
            'user_id'              => $bill->user_id,
            'amount'               => $amount,
            'paid_at'              => $paidAt ?: now(),
            'status'               => 'recorded',
            'receipt_no'           => $receiptNo,
            'received_by_admin_id' => $by ? $by->id : null,
        ]);
    }

    public function admin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        return Admin::create([
            'name'                => 'Maria Santos',
            'email'               => 'receipt-query-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);
    }
}
