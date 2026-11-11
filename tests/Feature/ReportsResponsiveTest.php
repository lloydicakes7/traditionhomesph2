<?php

namespace Tests\Feature;

use App\Enums\HouseholdType;
use App\Enums\JournalEntryStatus;
use App\Http\Livewire\Admin\Reports\StatementOfAccount;
use App\Models\Account\Account;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\JournalEntry\JournalEntry;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * What every report page looks like on a screen too small for its table.
 *
 * A report table is two to fifteen columns of figures, which no phone can hold.
 * Below `xl` each report stands its table down and puts the same records up as
 * cards — or, where the table is a two-column list, as one list of labels and
 * figures (see `.report-table-view` / `.report-card-view` in reports.css and
 * `x-reports.cards` / `x-reports.facts`). Both are always rendered; the
 * stylesheet picks one. Nothing in the suite wraps a table in a
 * `.table-responsive` any more, so no report scrolls sideways on a phone or on
 * paper.
 *
 * These tests walk every page rather than sampling them: the pages that draw
 * their table behind a condition (the statement with nobody picked, a month
 * with nothing posted) are exactly the ones where a card view would go missing
 * unnoticed.
 */
class ReportsResponsiveTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    const PAGES = [
        'reports.community',
        'reports.residents',
        'reports.amenities',
        'reports.ar-aging',
        'reports.monthly-income',
        'reports.monthly-expenses',
        'reports.daily-collection',
        'reports.daily-income',
        'reports.daily-expenses',
        'reports.profit-and-loss',
        'reports.statement-of-account',
        'reports.cash-flow',
    ];

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
            'email'               => 'responsive-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }

    /**
     * A chart account and one posted entry, so the reports have figures.
     */
    private function books(): void
    {
        $cash = Account::create([
            'code' => '1010', 'name' => 'Cash on Hand', 'type' => 'asset', 'is_cash' => true,
        ]);

        $income = Account::create([
            'code' => '4100', 'name' => 'Dues Income', 'type' => 'revenue',
        ]);

        $entry = JournalEntry::create([
            'entry_no'     => 'CRJ-2026-0001',
            'entry_date'   => now()->toDateString(),
            'status'       => JournalEntryStatus::POSTED,
            'reference_no' => 'OR-000001',
            'memo'         => 'Monthly dues for June',
        ]);

        $entry->lines()->createMany([
            ['account_id' => $cash->id, 'account_name' => $cash->name, 'side' => 'debit', 'amount' => 500],
            ['account_id' => $income->id, 'account_name' => $income->name, 'side' => 'credit', 'amount' => 500],
        ]);
    }

    public function test_every_report_renders_without_a_scroll_container(): void
    {
        $this->signIn();
        $this->books();

        foreach (self::PAGES as $page) {
            $this->get(route($page))
                ->assertOk('The ' . $page . ' page did not render.')
                ->assertDontSee('table-responsive');
        }
    }

    public function test_every_report_that_draws_a_table_draws_its_cards_beside_it(): void
    {
        $this->signIn();
        $this->books();

        // The statement of account draws nothing until a homeowner is picked —
        // it is read through its component below, where one can be.
        $pages = array_diff(self::PAGES, ['reports.statement-of-account']);

        foreach ($pages as $page) {
            $this->get(route($page))
                ->assertSee('report-table-view')
                ->assertSee('report-card-view');
        }
    }

    public function test_a_card_carries_the_same_record_as_the_row_it_stands_in_for(): void
    {
        $this->signIn();

        // One receipt on the register: the same reference has to read twice on
        // the page — once in the table, once in the card that replaces its row.
        $address = Address::create([
            'label'       => 'Unit 7-12',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Poblacion',
            'city'        => 'Santa Rosa',
            'province'    => 'Laguna',
            'region'      => 'IV-A',
        ]);

        $property = Property::create(['block' => 7, 'lot' => 12, 'address_id' => $address->id]);

        $resident = factory(\App\Models\Resident::class)->create();
        $homeowner = factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $property->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');

        $bill = Bill::create([
            'reference'      => 'INV-2026-0200',
            'user_id'        => $homeowner->id,
            'billed_to_name' => $homeowner->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 500,
            'total'          => 500,
            'paid_amount'    => 500,
            'balance'        => 0,
            'status'         => 'paid',
        ]);

        Payment::create([
            'bill_id'      => $bill->id,
            'user_id'      => $homeowner->id,
            'amount'       => 500,
            'paid_at'      => now(),
            'reference_no' => 'OR-000001',
        ]);

        $response = $this->get(route('reports.daily-collection'));

        $response->assertOk();

        // Twice on the page — the register's row, and the card that row becomes
        // — and the card view carries it, not just the table above it.
        $this->assertSame(2, substr_count($response->getContent(), 'OR-000001'));
        $response->assertSeeInOrder(['report-card-view', 'OR-000001']);
    }

    public function test_a_two_column_report_list_is_drawn_as_facts_on_a_small_screen(): void
    {
        $this->signIn();

        // "Residents profiled" is one line of the association's records, and
        // the facts list carries it: the label is read after the card view
        // opens, not only in the table above it.
        $this->get(route('reports.community'))
            ->assertOk()
            ->assertSeeInOrder(['report-card-view', 'Residents profiled']);
    }

    public function test_the_statement_of_account_draws_its_charges_as_cards_too(): void
    {
        $this->signIn();

        $owing = factory(User::class)->create();

        Bill::create([
            'reference'      => 'INV-2026-0001',
            'user_id'        => $owing->id,
            'billed_to_name' => $owing->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 500,
            'total'          => 500,
            'paid_amount'    => 0,
            'balance'        => 500,
            'status'         => 'unpaid',
        ]);

        Livewire::test(StatementOfAccount::class)
            ->set('userId', $owing->id)
            ->assertSee('report-table-view')
            ->assertSee('report-card-view')
            ->assertDontSee('table-responsive');
    }
}
