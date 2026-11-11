<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Payment;
use App\Models\Product\ProductCategory;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * A finance list's row is keyed by the record it shows, under a key of its own.
 *
 * This is what keeps a row's bootstrap dropdown working while the list
 * re-renders. A row that carries no `wire:key` is matched by the DOM diff
 * positionally, so when the mass-delete toolbar appears above the table the
 * diff can keep a row's toggle button while replacing the menu beside it —
 * bootstrap caches the menu it was built with, so the toggle then opens a node
 * that is no longer on the page and the menu never shows again. Keying the row
 * (and the mobile card) by the record makes the diff morph the whole row in
 * place, so the toggle and its menu stay the pair they were built as. The
 * Requests page already does this — see tests/Feature/SupportListRowKeyTest.php.
 *
 * The tests here assert the key on each finance list, and that a key is never
 * also the id of a control inside its row: Livewire reads `wire:key` where it
 * is there and falls back to the node's DOM `id` everywhere else, so a key that
 * doubles as an inner id is not a key at all.
 */
class FinanceListRowKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invoices_list_keys_its_rows_apart_from_the_ids_inside_them(): void
    {
        $this->signIn();

        $bill = $this->bill();

        $html = $this->get(route('collections.invoices'))->assertOk()->getContent();

        $this->assertKeysAreApartFromTheIdsInsideThem($html, 'invoice-row-' . $bill->id);
        $this->assertStringContainsString('wire:key="invoice-card-' . $bill->id . '"', $html);
    }

    public function test_the_payments_list_keys_its_rows_apart_from_the_ids_inside_them(): void
    {
        $this->signIn();

        $payment = $this->payment();

        $html = $this->get(route('collections.payments'))->assertOk()->getContent();

        $this->assertKeysAreApartFromTheIdsInsideThem($html, 'payment-row-' . $payment->id);
        $this->assertStringContainsString('wire:key="payment-card-' . $payment->id . '"', $html);
    }

    public function test_the_product_categories_list_keys_its_rows_apart_from_the_ids_inside_them(): void
    {
        $this->signIn();

        $category = ProductCategory::create(['name' => 'Dues', 'description' => '']);

        $html = $this->get(route('collections.product-categories'))->assertOk()->getContent();

        $this->assertKeysAreApartFromTheIdsInsideThem($html, 'product-category-row-' . $category->id);
        $this->assertStringContainsString('wire:key="product-category-card-' . $category->id . '"', $html);
    }

    public function test_the_expenses_list_keys_its_rows_apart_from_the_ids_inside_them(): void
    {
        $this->signIn();

        $expense = $this->expense();

        $html = $this->get(route('expenses.index'))->assertOk()->getContent();

        $this->assertKeysAreApartFromTheIdsInsideThem($html, 'expense-row-' . $expense->id);
        $this->assertStringContainsString('wire:key="expense-card-' . $expense->id . '"', $html);
    }

    public function test_the_expense_categories_list_keys_its_rows_apart_from_the_ids_inside_them(): void
    {
        $this->signIn();

        $category = ExpenseCategory::create(['name' => 'Security', 'description' => '']);

        $html = $this->get(route('expenses.categories'))->assertOk()->getContent();

        $this->assertKeysAreApartFromTheIdsInsideThem($html, 'expense-category-row-' . $category->id);
        $this->assertStringContainsString('wire:key="expense-category-card-' . $category->id . '"', $html);
    }

    public function test_the_suppliers_list_keys_its_rows_apart_from_the_ids_inside_them(): void
    {
        $this->signIn();

        $supplier = Supplier::create(['name' => 'Acme Security']);

        $html = $this->get(route('expenses.suppliers'))->assertOk()->getContent();

        $this->assertKeysAreApartFromTheIdsInsideThem($html, 'supplier-row-' . $supplier->id);
        $this->assertStringContainsString('wire:key="supplier-card-' . $supplier->id . '"', $html);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * No keyed row carries a key that is also the id of something inside it —
     * and `$expectedRowKey` is the key the record we filed sits under, so the
     * check never passes on an empty table.
     */
    private function assertKeysAreApartFromTheIdsInsideThem(string $html, string $expectedRowKey): void
    {
        preg_match_all('/<tr\b[^>]*wire:key="([^"]+)"[^>]*>(.*?)<\/tr>/s', $html, $rows, PREG_SET_ORDER);

        foreach ($rows as $row) {
            preg_match_all('/id="([^"]+)"/', $row[2], $ids);

            $this->assertNotContains(
                $row[1],
                $ids[1],
                "The row keyed \"{$row[1]}\" is also the id of a control inside it."
            );
        }

        $this->assertStringContainsString(
            'wire:key="' . $expectedRowKey . '"',
            $html,
            "The record we filed is not on the page under the row key \"{$expectedRowKey}\", so the rows above were not the ones checked."
        );
    }

    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'finance-row-key-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }

    private function bill(): Bill
    {
        return Bill::create([
            'reference'      => 'INV-2026-0001',
            'user_id'        => factory(User::class)->create()->id,
            'billed_to_name' => 'Ana Reyes',
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 100,
            'total'          => 100,
            'balance'        => 100,
            'status'         => 'unpaid',
        ]);
    }

    private function expense(): Expense
    {
        return Expense::create([
            'reference'      => 'EXP-2026-0001',
            'expense_date'   => now()->subDay(),
            'supplier_id'    => Supplier::create(['name' => 'Acme Security'])->id,
            'description'    => 'Gate guard wages',
            'subtotal'       => 350,
            'payment_method' => 'cash',
            'status'         => 'draft',
        ]);
    }

    private function payment(): Payment
    {
        $bill = $this->bill();

        return Payment::create([
            'bill_id'      => $bill->id,
            'user_id'      => $bill->user_id,
            'amount'       => 100,
            'reference_no' => 'OR-2026-0001',
            'paid_at'      => now(),
            'status'       => 'recorded',
        ]);
    }
}
