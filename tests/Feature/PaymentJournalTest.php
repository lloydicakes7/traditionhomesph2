<?php

namespace Tests\Feature;

use App\Actions\Accounting\JournalEntries\PostPaymentJournal;
use App\Actions\Collections\Invoices\CreateInvoice;
use App\Actions\Collections\Payments\RecordPayment;
use App\Enums\BillType;
use App\Enums\JournalSource;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Recording a receipt does not touch the books. Accounting posts it separately,
 * choosing the cash account the money moved through.
 */
class PaymentJournalTest extends TestCase
{
    use RefreshDatabase;

    private function bill(string $type, array $items): Bill
    {
        $bill = Bill::create([
            'reference' => Bill::nextReference($type === BillType::MONTHLY_DUE ? BillType::MONTHLY_DUE() : BillType::INVOICE()),
            'type'      => $type,
            'bill_date' => now()->toDateString(),
            'subtotal'  => array_sum(array_column($items, 'amount')),
            'total'     => array_sum(array_column($items, 'amount')),
            'balance'   => array_sum(array_column($items, 'amount')),
            'status'    => 'unpaid',
        ]);

        $bill->items()->createMany($items);

        return $bill;
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'officer-' . uniqid() . '@example.test',
            'password'            => bcrypt('Secret123!'),
            'password_changed_at' => now(),
        ]);
    }

    private function bank(): Account
    {
        return Account::create([
            'code'        => '1010',
            'name'        => 'Cash in Bank — BPI',
            'type'        => 'asset',
            'description' => '',
            'is_cash'     => true,
            'is_active'   => true,
        ]);
    }

    private function record(Bill $bill, $amount, Admin $admin): Payment
    {
        return app(RecordPayment::class)->handle($bill, [
            'amount'  => $amount,
            'paid_at' => now(),
        ], $admin);
    }

    private function postPayment(Payment $payment, Account $account, Admin $admin): Payment
    {
        return app(PostPaymentJournal::class)->handle($payment, [
            'account_id' => $account->id,
        ], $admin);
    }

    public function test_recording_a_payment_leaves_the_books_untouched(): void
    {
        $bill = $this->bill('invoice', [[
            'name' => 'Clearance', 'quantity' => 1, 'unit_price' => 200, 'amount' => 200,
        ]]);

        $payment = $this->record($bill, '200.00', $this->admin());

        $this->assertNull($payment->journal_entry_id);
        $this->assertSame(0, DB::table('journal_entries')->count());
    }

    public function test_accounting_posts_a_payment_to_the_chosen_cash_account(): void
    {
        $income = Account::create([
            'code' => '4100', 'name' => 'IDs and Stickers Income', 'type' => 'income',
            'description' => '', 'is_cash' => false, 'is_active' => true,
        ]);

        $category = ProductCategory::create(['name' => 'IDs', 'account_id' => $income->id]);
        $product = Product::create([
            'product_category_id' => $category->id, 'name' => 'Sticker', 'type' => 'product',
            'price' => 100, 'is_active' => true,
        ]);

        $bill = $this->bill('invoice', [[
            'product_id' => $product->id, 'name' => 'Sticker', 'quantity' => 1, 'unit_price' => 100, 'amount' => 100,
        ]]);

        $bank = $this->bank();
        $admin = $this->admin();
        $payment = $this->record($bill, '100.00', $admin);

        $this->assertSame(0, DB::table('journal_entries')->count());

        $posted = $this->postPayment($payment, $bank, $admin);

        $this->assertNotNull($posted->journal_entry_id);
        $this->assertSame(1, DB::table('journal_entries')->count());
        $this->assertSame(2, DB::table('journal_lines')->count());
        $this->assertDatabaseHas('journal_entries', [
            'id'          => $posted->journal_entry_id,
            'source_type' => JournalSource::PAYMENT,
            'source_id'   => $payment->id,
        ]);

        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $posted->journal_entry_id,
            'account_id'       => $bank->id,
            'side'             => 'debit',
            'amount'           => 100,
        ]);
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $posted->journal_entry_id,
            'account_id'       => $income->id,
            'side'             => 'credit',
            'amount'           => 100,
        ]);
    }

    public function test_a_monthly_due_payment_credits_the_dues_income_account(): void
    {
        $bill = $this->bill('monthly_due', [[
            'name' => 'Monthly Dues June 2026', 'quantity' => 1, 'unit_price' => 500, 'amount' => 500,
        ]]);

        $admin = $this->admin();
        $payment = $this->record($bill, '500.00', $admin);
        $posted = $this->postPayment($payment, Account::cashOnHand(), $admin);

        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $posted->journal_entry_id,
            'account_id'       => Account::duesIncome()->id,
            'side'             => 'credit',
            'amount'           => 500,
        ]);
    }

    public function test_a_partial_payment_splits_across_the_bills_categories_and_balances(): void
    {
        $income = Account::create([
            'code' => '4100', 'name' => 'Income', 'type' => 'income',
            'description' => '', 'is_cash' => false, 'is_active' => true,
        ]);
        $category = ProductCategory::create(['name' => 'Fees', 'account_id' => $income->id]);
        $product = Product::create([
            'product_category_id' => $category->id, 'name' => 'Clearance', 'type' => 'service',
            'price' => 200, 'is_active' => true,
        ]);

        $bill = $this->bill('invoice', [[
            'product_id' => $product->id, 'name' => 'Clearance', 'quantity' => 1, 'unit_price' => 200, 'amount' => 200,
        ]]);

        $admin = $this->admin();
        $payment = $this->record($bill, '50.00', $admin);
        $posted = $this->postPayment($payment, Account::cashOnHand(), $admin);

        $debits = DB::table('journal_lines')->where('journal_entry_id', $posted->journal_entry_id)
            ->where('side', 'debit')->sum('amount');
        $credits = DB::table('journal_lines')->where('journal_entry_id', $posted->journal_entry_id)
            ->where('side', 'credit')->sum('amount');

        $this->assertSame(50.0, round($debits, 2));
        $this->assertSame(50.0, round($credits, 2));
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $posted->journal_entry_id,
            'account_id'       => $income->id,
            'side'             => 'credit',
            'amount'           => 50,
        ]);
    }

    public function test_an_amenity_receipt_credits_the_amenities_income_account(): void
    {
        $bill = $this->bill('amenity_booking', [[
            'name' => 'Clubhouse booking', 'quantity' => 1, 'unit_price' => 1500, 'amount' => 1500,
        ]]);

        $amenities = Account::amenityIncome();
        $admin = $this->admin();
        $payment = $this->record($bill, '1500.00', $admin);
        $posted = $this->postPayment($payment, Account::cashOnHand(), $admin);

        $this->assertSame('Amenities', $amenities->name);
        $this->assertDatabaseHas('journal_lines', [
            'journal_entry_id' => $posted->journal_entry_id,
            'account_id'       => $amenities->id,
            'account_name'     => 'Amenities',
            'side'             => 'credit',
            'amount'           => 1500,
        ]);
    }

    public function test_posting_a_payment_requires_a_money_account(): void
    {
        $bill = $this->bill('invoice', [[
            'name' => 'Clearance', 'quantity' => 1, 'unit_price' => 200, 'amount' => 200,
        ]]);

        $income = Account::create([
            'code' => '4100', 'name' => 'Income', 'type' => 'income',
            'description' => '', 'is_cash' => false, 'is_active' => true,
        ]);

        $admin = $this->admin();
        $payment = $this->record($bill, '200.00', $admin);

        $this->expectException(ValidationException::class);

        try {
            $this->postPayment($payment, $income, $admin);
        } finally {
            $this->assertNull($payment->fresh()->journal_entry_id);
            $this->assertSame(0, DB::table('journal_entries')->count());
        }
    }

    public function test_raising_an_invoice_leaves_the_books_to_the_receipt(): void
    {
        $user = factory(\App\Models\User::class)->create();
        $product = Product::create([
            'name' => 'Clearance', 'type' => 'fee', 'price' => 200, 'is_active' => true,
        ]);

        app(CreateInvoice::class)->handle([
            'scope'     => 'single',
            'user_id'   => $user->id,
            'bill_date' => now()->toDateString(),
            'items'     => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 200]],
        ], $this->admin());

        $this->assertSame(0, DB::table('journal_entries')->count());
    }
}
