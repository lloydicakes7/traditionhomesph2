<?php

namespace Tests\Feature\Actions\Expenses;

use App\Actions\Expenses\ExpenseCategories\CreateExpenseCategory;
use App\Actions\Expenses\ExpenseCategories\DeleteExpenseCategories;
use App\Actions\Expenses\ExpenseCategories\UpdateExpenseCategory;
use App\Actions\Expenses\Suppliers\CreateSupplier;
use App\Actions\Expenses\Suppliers\DeleteSuppliers;
use App\Actions\Expenses\Suppliers\UpdateSupplier;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CatalogActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Categories =====

    public function test_a_new_category_opens_its_own_expense_account(): void
    {
        $admin = $this->admin();

        $category = app(CreateExpenseCategory::class)->handle([
            'name'        => 'Security Services',
            'description' => 'Guards and roving',
        ], $admin);

        $this->assertNotNull($category->account_id);
        $this->assertSame('Security Services', Account::find($category->account_id)->name);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'expense_category',
        ]);
    }

    public function test_renaming_a_category_renames_its_expense_account(): void
    {
        $admin = $this->admin();
        $category = app(CreateExpenseCategory::class)->handle(['name' => 'Utilities'], $admin);

        $renamed = app(UpdateExpenseCategory::class)->handle($category, [
            'name' => 'Utilities and Power',
        ], $admin);

        $this->assertSame('Utilities and Power', $renamed->name);
        $this->assertSame('Utilities and Power', Account::find($renamed->account_id)->name);
    }

    public function test_a_category_used_by_an_expense_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $category = app(CreateExpenseCategory::class)->handle(['name' => 'Supplies'], $admin);

        $expense = Expense::create([
            'reference'    => 'EXP-' . now()->year . '-0001',
            'expense_date' => now()->toDateString(),
            'subtotal'     => 120,
            'status'       => 'draft',
        ]);

        ExpenseItem::create([
            'expense_id'          => $expense->id,
            'expense_category_id' => $category->id,
            'category_name'       => 'Supplies',
            'description'         => 'Printer paper',
            'amount'              => 120,
        ]);

        try {
            app(DeleteExpenseCategories::class)->handle([$category->id], $admin);
            $this->fail('A category that expenses use should not be deleted.');
        } catch (ValidationException $exception) {
            $this->assertNotNull(ExpenseCategory::find($category->id));
            $this->assertDatabaseHas('expense_items', [
                'description'   => 'Printer paper',
                'category_name' => 'Supplies',
            ]);
        }
    }

    public function test_an_unused_category_can_be_deleted(): void
    {
        $admin = $this->admin();
        $category = app(CreateExpenseCategory::class)->handle(['name' => 'Supplies'], $admin);

        $this->assertSame(1, app(DeleteExpenseCategories::class)->handle([$category->id], $admin));
        $this->assertNull(ExpenseCategory::find($category->id));
    }

    // ===== Suppliers =====

    public function test_a_supplier_can_be_added_renamed_and_removed(): void
    {
        $admin = $this->admin();

        $supplier = app(CreateSupplier::class)->handle(['name' => 'JM Builders'], $admin);

        $renamed = app(UpdateSupplier::class)->handle($supplier, [
            'name'        => 'JM Builders and Supply',
            'description' => 'Roofing and repainting',
        ], $admin);

        $this->assertSame('JM Builders and Supply', $renamed->name);
        $this->assertSame(1, app(DeleteSuppliers::class)->handle([$renamed->id], $admin));
        $this->assertNull(Supplier::find($renamed->id));
    }

    public function test_a_supplier_named_by_an_expense_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $supplier = app(CreateSupplier::class)->handle(['name' => 'JM Builders'], $admin);

        Expense::create([
            'reference'    => 'EXP-' . now()->year . '-0001',
            'expense_date' => now()->toDateString(),
            'supplier_id'  => $supplier->id,
            'subtotal'     => 120,
            'status'       => 'draft',
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(DeleteSuppliers::class)->handle([$supplier->id], $admin);
        } finally {
            $this->assertNotNull(Supplier::find($supplier->id));
        }
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'expense-catalog-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }
}
