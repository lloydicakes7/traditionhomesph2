<?php

namespace Tests\Feature;

use App\Enums\HouseholdType;
use App\Http\Livewire\Admin\Collections\Payments\PaymentTableList;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The payments ledger's CSV export.
 *
 * The export used to eager-load relations a Payment never had (`invoice`,
 * `monthlyDue`, `property`), so the download died before writing a row, and
 * the unit column read a `property_label` the model did not define. Both are
 * fixed at the source: the export loads the relations the ledger actually has
 * (`bill.user.household.property`), and the model owns its own label.
 */
class PaymentExportTest extends TestCase
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
            'email'               => 'payment-export-' . uniqid() . '@example.test',
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

    public function test_the_export_reads_the_ledgers_own_relations_and_writes_the_unit()
    {
        $this->signIn();

        $resident = factory(Resident::class)->create();
        $user = factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $this->unit(3, 9)->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');

        $bill = Bill::create([
            'reference'      => 'INV-' . now()->year . '-0001',
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 750,
            'total'          => 750,
            'balance'        => 750,
            'status'         => 'unpaid',
        ]);

        Payment::create([
            'bill_id'      => $bill->id,
            'user_id'      => $user->id,
            'amount'       => 750,
            'paid_at'      => now(),
            'reference_no' => 'OR-000001',
        ]);

        $component = Livewire::test(PaymentTableList::class);

        $method = new \ReflectionMethod(PaymentTableList::class, 'exportCsv');
        $method->setAccessible(true);
        $csv = $method->invoke($component->instance());

        $this->assertStringContainsString('OR-000001', $csv);
        $this->assertStringContainsString($bill->reference, $csv);
        $this->assertStringContainsString('Blk 3 Lot 9', $csv);
        $this->assertStringContainsString('₱750.00', $csv);
    }

    public function test_the_unit_column_says_dash_when_the_account_holds_no_household()
    {
        $user = factory(User::class)->create();   // profiled, but no household yet

        $bill = Bill::create([
            'reference'      => 'INV-' . now()->year . '-0002',
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 350,
            'total'          => 350,
            'balance'        => 350,
            'status'         => 'unpaid',
        ]);

        $payment = Payment::create([
            'bill_id'      => $bill->id,
            'user_id'      => $user->id,
            'amount'       => 350,
            'paid_at'      => now(),
            'reference_no' => 'OR-000002',
        ]);

        // No household on file: the column says so rather than showing nothing.
        $this->assertNull($payment->bill->property);
        $this->assertSame('—', $payment->property_label);
    }
}
