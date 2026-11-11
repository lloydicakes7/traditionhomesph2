<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\HouseholdType;
use App\Http\Livewire\Admin\Reports\AmenityReport;
use App\Http\Livewire\Admin\Reports\Community;
use App\Http\Livewire\Admin\Reports\DailyCollection;
use App\Http\Livewire\Admin\Reports\MonthlyIncome;
use App\Http\Livewire\Admin\Reports\ResidentReport;
use App\Http\Livewire\Admin\Reports\StatementOfAccount;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
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
 * The Reports pages the office reads by hand: the community snapshot, the
 * residents population, the amenities, the day's collection register, and the
 * statement of account.
 *
 * Each test pins the path its figures are read by — an amenity's takings come
 * from the booking's own bill (the booking holds no amount column), a payment
 * reaches its unit through the paying account (a Payment has no property
 * relation), the household mix is counted per unit rather than per resident,
 * and the statement picker lists homeowner accounts rather than units, so an
 * account with something due is stated even while it occupies no household.
 */
class ReportsPagesTest extends TestCase
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
            'email'               => 'reports-' . uniqid() . '@example.test',
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
     * A bill an account owes, open for the current month.
     */
    private function openBill(User $user, float $total, string $type = 'monthly_due'): Bill
    {
        return Bill::create([
            'reference'      => 'DUE-' . now()->format('Ym') . '-' . str_pad((string) Bill::count() + 1, 4, '0', STR_PAD_LEFT),
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => $type,
            'billing_period' => $type === 'monthly_due' ? now()->format('Y-m') : null,
            'bill_date'      => now()->toDateString(),
            'subtotal'       => $total,
            'total'          => $total,
            'paid_amount'    => 0,
            'balance'        => $total,
            'status'         => 'unpaid',
        ]);
    }

    public function test_the_amenities_report_reads_an_amenities_takings_from_its_completed_bookings()
    {
        $this->signIn();

        $amenity = Amenity::create([
            'name'        => 'Clubhouse',
            'rate_type'   => 'fixed',
            'rate'        => 1500,
            'is_bookable' => true,
        ]);

        // The booking's bill carries the rate and the deposit as separate
        // lines — the amenity earned the rate, not the deposit it also holds.
        $bill = Bill::create([
            'reference' => 'AMN-' . now()->year . '-0001',
            'type'      => 'amenity_booking',
            'bill_date' => now()->toDateString(),
            'subtotal'  => 2000,
            'total'     => 2000,
            'status'    => 'paid',
        ]);

        $bill->items()->createMany([
            ['name' => 'Clubhouse rate', 'quantity' => 1, 'unit_price' => 1500, 'amount' => 1500],
            ['name' => 'Deposit', 'quantity' => 1, 'unit_price' => 500, 'amount' => 500],
        ]);

        AmenityBooking::create([
            'amenity_id'     => $amenity->id,
            'bill_id'        => $bill->id,
            'starts_at'      => now()->subDay()->setTime(8, 0),
            'ends_at'        => now()->subDay()->setTime(12, 0),
            'deposit_amount' => 500,
            'status'         => BookingStatus::COMPLETED,
        ]);

        $report = Livewire::test(AmenityReport::class)->viewData('report');

        $this->assertSame('₱1,500.00', $report['earnings']);
        $this->assertSame(1500.0, (float) $report['by_amenity'][0]['earned']);
    }

    public function test_the_community_report_holds_the_associations_own_figures_and_no_amenities()
    {
        $this->signIn();

        // One unit taken up, one left empty: the occupancy split is the units
        // the KPI row already counts.
        $resident = factory(Resident::class)->create();
        factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $this->unit(7, 12)->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');
        $this->unit(8, 1);

        $report = Livewire::test(Community::class)->viewData('report');

        // The amenity figures are the Amenities Report's now, not this page's.
        $this->assertArrayNotHasKey('amenities', $report);

        $this->assertSame([
            ['label' => 'Occupied', 'value' => 1, 'theme' => 'success'],
            ['label' => 'Vacant', 'value' => 1, 'theme' => 'secondary'],
        ], $report['occupancy']);

        // One list of records, read by the page's chart and its table alike.
        $this->assertSame('Residents profiled', $report['records'][0]['label']);
        $this->assertSame(2, $report['records'][2]['value']);   // the units on file
    }

    /**
     * The portal's take-up and the year's growth are the Community Report's
     * readings: the Residents Report is about the people, and a chart of accounts
     * belongs beside the counters of the page that states them.
     */
    public function test_the_community_report_carries_the_portal_and_the_years_growth(): void
    {
        $this->signIn();

        $this->unit(7, 12);

        // One account on file and one resident profiled without one: the split
        // is the same pair of figures the KPI row above it counts.
        factory(User::class)->create();
        factory(Resident::class)->create();

        $report = Livewire::test(Community::class)->viewData('report');

        $this->assertSame([
            ['label' => 'With an account', 'value' => 1, 'theme' => 'success'],
            ['label' => 'No account yet', 'value' => 1, 'theme' => 'secondary'],
        ], $report['adoption']);

        // A line is one point per month of the year, the month just joined in
        // included — and the Residents Report carries neither reading any more.
        $this->assertCount(12, $report['registrations']);
        $this->assertSame('Jan', $report['registrations'][0]['label']);
        $this->assertSame(2, $report['registrations'][(int) now()->format('n') - 1]['value']);

        $residents = Livewire::test(ResidentReport::class)->viewData('report');

        $this->assertArrayNotHasKey('portal', $residents);
        $this->assertArrayNotHasKey('registrations', $residents);
    }

    public function test_the_residents_report_counts_the_household_mix_by_unit_not_by_resident()
    {
        $this->signIn();

        $occupied = $this->unit(7, 12);
        $this->unit(8, 1);
        $this->unit(8, 2);

        $resident = factory(Resident::class)->create();
        factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $occupied->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');

        // Three people profiled as members of that same household: they live in
        // an owner-held unit, but the office recorded one household for it — the
        // homeowner's own account — not four owners.
        foreach (range(1, 3) as $member) {
            HouseholdMember::create([
                'household_id'         => $household->id,
                'resident_id'          => factory(Resident::class)->create()->id,
                'relationship' => 'son',
            ]);
        }

        $report = Livewire::test(ResidentReport::class)->viewData('report');

        $this->assertSame([
            ['label' => 'Not specified', 'value' => 2],   // the two units with no household
            ['label' => 'Owner', 'value' => 1],
        ], $report['household_type']);

        // The plain numbers below the charts read the same snapshot.
        $this->assertContains(['label' => 'Occupied units', 'value' => 1], $report['statistics']);
        $this->assertContains(['label' => 'Vacant units', 'value' => 2], $report['statistics']);
    }

    public function test_the_monthly_income_breakdown_is_drawn_without_a_scroll_container()
    {
        $this->signIn();

        Livewire::test(MonthlyIncome::class)
            ->assertSee('Income by Account')
            // A fixed table on a wide screen and one card per account below it,
            // as the Monthly Dues Table View is drawn — never a scroll box, so
            // the year's far months are not clipped on paper.
            ->assertSee('report-table-view')
            ->assertSee('report-card-view')
            ->assertDontSee('table-responsive');
    }

    public function test_the_community_residents_and_amenities_reports_render_their_pages()
    {
        $this->signIn();

        // Each page shell mounts its own component; the sidebar offers all three
        // under Reports.
        $this->get(route('reports.community'))
            ->assertOk()
            ->assertSee('Records on File')
            // The portal's take-up and the year's growth are charted on this page.
            ->assertSee('Portal Adoption')
            ->assertSee('Residents Profiled This Year')
            ->assertDontSee('Amenity Bookings');

        // …and only here: the Residents Report is about the people, and a chart
        // it no longer carries is not left standing on it either.
        $this->get(route('reports.residents'))
            ->assertOk()
            ->assertSee('Residents Report in Numbers')
            ->assertDontSee('Portal Adoption')
            ->assertDontSee('Residents Profiled This Year');

        $this->get(route('reports.amenities'))
            ->assertOk()
            ->assertSee('Amenities on File')
            ->assertSee('Amenity Bookings');
    }

    public function test_the_daily_collection_register_reaches_the_unit_through_the_paying_account()
    {
        $this->signIn();

        // One account occupies a unit; one has no household on file at all.
        $occupantResident = factory(Resident::class)->create();
        $occupant = factory(User::class)->create(['resident_id' => $occupantResident->id]);

        $household = Household::create([
            'property_id'      => $this->unit(7, 12)->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($occupantResident->id, 'father');

        $homeless = factory(User::class)->create();

        Payment::create([
            'bill_id'      => $this->openBill($occupant, 500, 'invoice')->id,
            'user_id'      => $occupant->id,
            'amount'       => 500,
            'paid_at'      => now(),
            'reference_no' => 'OR-000001',
        ]);

        Payment::create([
            'bill_id'      => $this->openBill($homeless, 350, 'invoice')->id,
            'user_id'      => $homeless->id,
            'amount'       => 350,
            'paid_at'      => now(),
            'reference_no' => 'OR-000002',
        ]);

        Livewire::test(DailyCollection::class)
            ->assertSee('OR-000001')
            ->assertSee('OR-000002')
            ->assertSee('Blk 7 Lot 12')
            ->assertSee($occupant->resident->full_name)
            ->assertSee($homeless->resident->full_name);
    }

    public function test_the_statement_picker_lists_homeowners_with_a_balance_even_without_a_household()
    {
        $this->signIn();

        $owing = factory(User::class)->create();   // no household, but a bill due
        $idle  = factory(User::class)->create();   // nothing outstanding

        $this->openBill($owing, 500);

        Livewire::test(StatementOfAccount::class)
            // The account with something due is offered, the idle one is not.
            ->assertSee($owing->email)
            ->assertDontSee($idle->email)
            // Picking it reads the account's own statement for the period.
            ->set('userId', $owing->id)
            ->assertSee($owing->resident->full_name)
            ->assertSee('₱500.00');
    }
}
