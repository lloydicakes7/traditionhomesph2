<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Expenses\Modals\ExpenseWizardModal;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseItem;
use App\Models\Role;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Record Expense wizard, opened to edit an expense that already exists.
 *
 * The wizard binds its supplier to form.supplier_id, so pre-filling it with
 * anything else leaves the select on "Not specified" — and saving that edit
 * would quietly drop the supplier off the expense.
 */
class ExpenseWizardModalTest extends TestCase
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
            'email'               => 'officer' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * A draft expense filed under the given supplier.
     */
    private function expenseFor(Supplier $supplier): Expense
    {
        $expense = Expense::create([
            'reference'    => 'EXP-2026-0001',
            'expense_date' => now()->subDay(),
            'supplier_id'  => $supplier->id,
            'description'  => 'Gate guard wages',
            'subtotal'     => 350,
            'status'       => 'draft',
        ]);

        ExpenseItem::create([
            'expense_id'     => $expense->id,
            'category_name'  => 'Security',
            'description'    => 'Gate guard',
            'amount'         => 350,
        ]);

        return $expense;
    }

    public function test_opening_the_wizard_to_edit_preselects_the_existing_supplier()
    {
        $this->signIn();

        $supplier = Supplier::create(['name' => 'Acme Security']);
        $expense  = $this->expenseFor($supplier);

        Livewire::test(ExpenseWizardModal::class)
            ->call('openEdit', $expense->id)
            ->assertSet('expenseId', $expense->id)
            ->assertSet('form.supplier_id', (string) $supplier->id)
            // The select is offered that supplier as a choice; the choice is
            // made by wire:model, so the bound value is what preselects it.
            ->assertSee('<option value="' . $supplier->id . '">Acme Security</option>', false);
    }

    public function test_a_new_expense_starts_with_no_supplier_chosen()
    {
        $this->signIn();

        Supplier::create(['name' => 'Acme Security']);

        Livewire::test(ExpenseWizardModal::class)
            ->call('openCreate')
            ->assertSet('form.supplier_id', '')
            ->assertSee('Not specified', false);
    }

    public function test_saving_an_edit_keeps_the_supplier_it_was_filed_under()
    {
        $this->signIn();

        $supplier = Supplier::create(['name' => 'Acme Security']);
        $expense  = $this->expenseFor($supplier);

        Livewire::test(ExpenseWizardModal::class)
            ->call('openEdit', $expense->id)
            ->set('form.description', 'Gate guard wages — June')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expenses', [
            'id'           => $expense->id,
            'supplier_id'  => $supplier->id,
            'description'  => 'Gate guard wages — June',
        ]);
    }

    public function test_editing_keeps_a_supplier_that_was_renamed()
    {
        $this->signIn();

        $supplier = Supplier::create(['name' => 'Retired Vendor']);
        $expense  = $this->expenseFor($supplier);

        Livewire::test(ExpenseWizardModal::class)
            ->call('openEdit', $expense->id)
            ->assertSet('form.supplier_id', (string) $supplier->id)
            // Every supplier is offered, so the select keeps the one the
            // expense was filed under instead of falling back to "Not specified".
            ->assertSee('<option value="' . $supplier->id . '">Retired Vendor</option>', false);

        Livewire::test(ExpenseWizardModal::class)
            ->call('openEdit', $expense->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('expenses', [
            'id'          => $expense->id,
            'supplier_id' => $supplier->id,
        ]);
    }

}