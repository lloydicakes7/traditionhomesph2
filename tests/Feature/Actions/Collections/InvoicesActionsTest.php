<?php

namespace Tests\Feature\Actions\Collections;

use App\Actions\Collections\Invoices\CancelInvoice;
use App\Actions\Collections\Invoices\CreateInvoice;
use App\Actions\Collections\Invoices\DeleteInvoices;
use App\Actions\Collections\Invoices\UpdateInvoice;
use App\Enums\BillStatus;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvoicesActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Create =====

    public function test_creating_an_invoice_writes_the_bill_its_lines_and_the_activity_log(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();
        $product = $this->product('Sticker', 250);

        $invoices = app(CreateInvoice::class)->handle([
            'scope'     => 'single',
            'user_id'   => $user->id,
            'bill_date' => '2026-09-01',
            'items'     => [['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 250]],
        ], $admin);

        $invoice = $invoices->first();

        $this->assertSame(1, $invoices->count());
        $this->assertSame(500.0, (float) $invoice->total);
        $this->assertSame(1, $invoice->items()->count());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'invoice',
        ]);
    }

    public function test_creating_an_invoice_for_every_account_raises_one_bill_per_account(): void
    {
        $admin = $this->admin();
        $product = $this->product('Clearance', 100);

        factory(User::class)->create();
        factory(User::class)->create();

        $invoices = app(CreateInvoice::class)->handle([
            'scope'     => 'all',
            'bill_date' => '2026-09-01',
            'items'     => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100]],
        ], $admin);

        $this->assertSame(2, $invoices->count());
        $this->assertSame(2, Bill::query()->invoices()->count());
        $this->assertSame(2, \App\Models\AdminActivity::query()->where('module', 'invoice')->count());
    }

    // ===== Update =====

    public function test_updating_an_invoice_replaces_its_lines(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();
        $old = $this->product('Old', 100);
        $new = $this->product('New', 400);

        $invoice = app(CreateInvoice::class)->handle([
            'scope'     => 'single',
            'user_id'   => $user->id,
            'bill_date' => '2026-09-01',
            'items'     => [['product_id' => $old->id, 'quantity' => 1, 'unit_price' => 100]],
        ], $admin)->first();

        $updated = app(UpdateInvoice::class)->handle($invoice, [
            'bill_date' => '2026-09-02',
            'items'     => [['product_id' => $new->id, 'quantity' => 3, 'unit_price' => 400]],
        ], $admin);

        $this->assertSame(1, $updated->items()->count());
        $this->assertSame('New', $updated->items()->first()->name);
        $this->assertSame(1200.0, (float) $updated->total);
        $this->assertSame('2026-09-02', $updated->bill_date->toDateString());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'updated',
            'module'   => 'invoice',
        ]);
    }

    // ===== Cancel =====

    public function test_cancelling_an_invoice_withdraws_it(): void
    {
        $admin = $this->admin();
        $invoice = $this->openInvoice(factory(User::class)->create(), 700);

        $cancelled = app(CancelInvoice::class)->handle($invoice, $admin);

        $this->assertSame(BillStatus::CANCELLED, $cancelled->status);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'cancelled',
            'module'   => 'invoice',
        ]);
    }

    // ===== Delete =====

    public function test_deleting_an_invoice_leaves_a_paid_one_alone(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();
        $free = $this->openInvoice($user, 500);
        $paid = $this->openInvoice($user, 900);

        Payment::create([
            'bill_id' => $paid->id,
            'user_id' => $user->id,
            'amount'  => 900,
            'paid_at' => now(),
            'status'  => 'recorded',
        ]);

        $deleted = app(DeleteInvoices::class)->handle([$free->id, $paid->id], $admin);

        $this->assertSame(1, $deleted);
        $this->assertNull(Bill::find($free->id));
        $this->assertNotNull(Bill::find($paid->id));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'invoice',
        ]);
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'invoice-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    private function product(string $name, float $price): Product
    {
        return Product::create([
            'name'       => $name,
            'type'       => 'fee',
            'price'      => $price,
            'is_active'  => true,
        ]);
    }

    private function openInvoice(User $user, float $total): Bill
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
