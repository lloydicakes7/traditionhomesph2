<?php

namespace Tests\Feature\Queries;

use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Queries\Homeowners\Addresses\AddressListQuery;
use App\Queries\Homeowners\Homeowners\HomeownerListQuery;
use App\Queries\Homeowners\Homeowners\HomeownerStatsQuery;
use App\Queries\Homeowners\Households\HouseholdListQuery;
use App\Queries\Homeowners\Households\HouseholdMemberQuery;
use App\Queries\Homeowners\Properties\PropertyListQuery;
use App\Queries\Homeowners\Properties\PropertyStatsQuery;
use App\Queries\Homeowners\Residents\ResidentListQuery;
use App\Queries\Homeowners\Residents\ResidentStatsQuery;
use App\Queries\Homeowners\Users\UserLinkQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeownersQueryTest extends TestCase
{
    use RefreshDatabase;

    // ===== Residents =====

    public function test_the_resident_list_searches_sorts_and_resolves_selection(): void
    {
        $this->account($this->resident('Santos', 'Ana'));
        $this->resident('Dela Cruz', 'Juan');

        $query = app(ResidentListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate('Dela')->total());
        $this->assertCount(2, $query->ids());
        $this->assertCount(1, $query->ids('Santos'));
        $this->assertSame('Dela Cruz', $query->paginate('Dela')->items()[0]->surname);
    }

    public function test_the_resident_summary_counts_who_has_an_account(): void
    {
        $this->account($this->resident('Santos', 'Ana'));
        $this->resident('Dela Cruz', 'Juan');

        $stats = app(ResidentStatsQuery::class)->summary();

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['withAccount']);
        $this->assertSame(1, $stats['withoutAccount']);
        $this->assertSame(1, $stats['active']);
        $this->assertSame(0, $stats['disabled']);
    }

    // ===== Households =====

    public function test_the_household_list_searches_and_reads_one_household(): void
    {
        $address = $this->address();

        $firstResident = $this->resident('Santos', 'Ana');
        $first = $this->account($firstResident);
        $household = Household::create([
            'property_id'      => $this->property($address, 1, 1)->id,
            'type'             => 'owner',
        ]);
        $household->placeHead($firstResident->id, 'father');

        $secondResident = $this->resident('Dela Cruz', 'Juan');
        $second = $this->account($secondResident);
        $secondHousehold = Household::create([
            'property_id'      => $this->property($address, 2, 2)->id,
            'type'             => 'tenant',
        ]);
        $secondHousehold->placeHead($secondResident->id, 'mother');

        $query = app(HouseholdListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate('Santos')->total());
        $this->assertSame(1, $query->paginate('1')->total());

        $view = $query->view($household->id);

        $this->assertSame('Blk 1 Lot 1', $view['property']);
        $this->assertSame('Owner', $view['type']);
        $this->assertSame((int) $firstResident->id, (int) $view['homeowner_id']);
        $this->assertSame($household->id, $query->forUser($first->id)?->id);
    }

    public function test_the_member_picker_offers_only_residents_without_a_household(): void
    {
        $placed = $this->resident('Santos', 'Ana');
        $free = $this->resident('Dela Cruz', 'Juan');

        $this->household($placed);

        $query = app(HouseholdMemberQuery::class);

        $this->assertSame([$free->id], $query->selectable()->pluck('id')->all());
        $this->assertSame([], $query->selectable($free->id)->pluck('id')->all());
    }

    // ===== Properties =====

    public function test_the_property_list_filters_by_type_and_occupancy(): void
    {
        $address = $this->address();
        $occupied = $this->property($address, 1, 1, 'house');
        $this->property($address, 2, 2, 'lot');

        $this->household($this->resident('Santos', 'Ana'), $occupied);

        $query = app(PropertyListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate('', 'lot')->total());
        $this->assertSame(1, $query->paginate('', '', 'has_owner')->total());
        $this->assertSame(1, $query->paginate('', '', 'vacant')->total());
        $this->assertCount(2, $query->ids());

        $this->assertSame($occupied->id, $query->find($occupied->id)->id);
    }

    public function test_the_property_summary_counts_vacant_units(): void
    {
        $address = $this->address();
        $occupied = $this->property($address, 1, 1);
        $this->property($address, 2, 2);

        $this->household($this->resident('Santos', 'Ana'), $occupied);

        $stats = app(PropertyStatsQuery::class)->summary();

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['withOwner']);
        $this->assertSame(1, $stats['vacant']);
        $this->assertSame(1, $stats['addresses']);
    }

    // ===== Homeowners =====

    public function test_the_homeowner_list_reads_accounts_and_their_counts(): void
    {
        $first = $this->account($this->resident('Santos', 'Ana'));
        $this->account($this->resident('Dela Cruz', 'Juan'));

        $query = app(HomeownerListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate($first->email)->total());
        $this->assertCount(2, $query->ids());

        $row = $query->paginate($first->email)->items()[0];

        $this->assertSame(0, (int) $row->member_count);
        $this->assertNull($row->row_alert);
    }

    public function test_the_homeowner_summary_counts_active_accounts(): void
    {
        $this->account($this->resident('Santos', 'Ana'));
        $disabled = $this->account($this->resident('Dela Cruz', 'Juan'));
        $disabled->update(['is_active' => false]);

        $stats = app(HomeownerStatsQuery::class)->summary();

        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['active']);
        $this->assertSame(50, $stats['activePct']);
    }

    // ===== Addresses and links =====

    public function test_the_address_list_puts_the_primary_address_first(): void
    {
        $this->address('Secondary');
        $this->address('Primary', true);

        $rows = app(AddressListQuery::class)->paginate();

        $this->assertSame('Primary', $rows->items()[0]->label);
    }

    public function test_the_link_picker_offers_only_unlinked_records(): void
    {
        $this->account($this->resident('Santos', 'Ana'));
        $free = $this->resident('Dela Cruz', 'Juan');
        User::create([
            'resident_id' => null,
            'email'       => 'spare@example.test',
            'password'    => bcrypt('Secret123!'),
            'is_active'   => true,
        ]);

        $query = app(UserLinkQuery::class);

        $this->assertSame([$free->id], $query->unlinkedResidents()->pluck('id')->all());
        $this->assertSame(['spare@example.test'], $query->unlinkedUsers()->pluck('email')->all());
    }

    // ===== Helpers =====

    private function resident(string $surname, string $firstName): Resident
    {
        return factory(Resident::class)->create([
            'surname'    => $surname,
            'first_name' => $firstName,
        ]);
    }

    private function account(Resident $resident): User
    {
        return factory(User::class)->create(['resident_id' => $resident->id]);
    }

    private function household(Resident $head, ?Property $property = null): Household
    {
        $this->account($head);

        $household = Household::create([
            'property_id'      => ($property ?? $this->property($this->address(), rand(1, 90), rand(1, 90)))->id,
            'type'             => 'owner',
        ]);

        $household->placeHead($head->id, 'father');

        return $household;
    }

    private function address(string $label = 'THHA Subdivision', bool $primary = false): Address
    {
        return Address::create([
            'label'       => $label,
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'San Isidro',
            'city'        => 'Cabuyao',
            'province'    => 'Laguna',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
            'is_primary'  => $primary,
        ]);
    }

    private function property(Address $address, int $block, int $lot, string $type = 'house'): Property
    {
        return Property::create([
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $address->id,
            'sqm'        => 100,
            'type'       => $type,
        ]);
    }
}
