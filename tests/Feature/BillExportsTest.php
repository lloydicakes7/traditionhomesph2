<?php

namespace Tests\Feature;

use App\Enums\HouseholdType;
use App\Http\Livewire\Admin\Collections\Invoices\InvoiceTableList;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDueTableList;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The two bill lists' Unit column — their CSV exports and their table rows.
 *
 * A `Bill` carries no unit of its own: the unit is reached through the
 * account (`user.household.property`), so both lists read
 * `->property->block_lot_label` and say "—" while the account occupies no
 * household, rather than a `property_label` no model ever defined.
 */
class BillExportsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An officer with the panel's full access.
     */
    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'bill-export-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }

    /**
     * A unit on file — the factory leaves block and lot to the seeder, so the
     * test names them itself.
     */
    private function unit(int $block, int $lot): Property
    {
        $address = Address::create([
            'label'       => 'Unit ' . $block . '-' . $lot . '-' . uniqid(),
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Poblacion',
            'city'        => 'Santa Rosa',
            'province'    => 'Laguna',
            'region'      => 'IV-A',
        ]);

        return Property::create([
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $address->id,
        ]);
    }

    /**
     * A bill of the given kind for one account.
     */
    private function billFor(User $user, string $type, string $reference): Bill
    {
        return Bill::create([
            'reference'      => $reference,
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => $type,
            'billing_period' => $type === 'monthly_due' ? now()->format('Y-m') : null,
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 500,
            'total'          => 500,
            'balance'        => 500,
            'status'         => 'unpaid',
        ]);
    }

    /**
     * The CSV a list component builds for the current filters.
     */
    private function exportCsv(string $component): string
    {
        $testable = Livewire::test($component);

        $method = new \ReflectionMethod($component, 'exportCsv');
        $method->setAccessible(true);

        return $method->invoke($testable->instance());
    }

    public function test_both_exports_and_the_table_read_the_unit_through_the_accounts_household()
    {
        $this->signIn();

        $resident = factory(Resident::class)->create();
        $user = factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $this->unit(4, 2)->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');

        $due     = $this->billFor($user, 'monthly_due', 'DUE-' . now()->format('Ym') . '-0001');
        $invoice = $this->billFor($user, 'invoice', 'INV-' . now()->year . '-0001');

        // Each export writes the unit right after the reference.
        $this->assertStringContainsString(
            '"' . $due->reference . '","Blk 4 Lot 2"',
            $this->exportCsv(MonthlyDueTableList::class)
        );

        $this->assertStringContainsString(
            '"' . $invoice->reference . '","Blk 4 Lot 2"',
            $this->exportCsv(InvoiceTableList::class)
        );

        // The list itself shows the same unit.
        Livewire::test(MonthlyDueTableList::class)->assertSee('Blk 4 Lot 2');
        Livewire::test(InvoiceTableList::class)->assertSee('Blk 4 Lot 2');
    }

    public function test_the_unit_column_reads_dash_when_the_account_holds_no_household()
    {
        $this->signIn();

        $user = factory(User::class)->create();   // profiled, but no household yet

        $due     = $this->billFor($user, 'monthly_due', 'DUE-' . now()->format('Ym') . '-0002');
        $invoice = $this->billFor($user, 'invoice', 'INV-' . now()->year . '-0002');

        $this->assertStringContainsString(
            '"' . $due->reference . '","—"',
            $this->exportCsv(MonthlyDueTableList::class)
        );

        $this->assertStringContainsString(
            '"' . $invoice->reference . '","—"',
            $this->exportCsv(InvoiceTableList::class)
        );
    }
}
