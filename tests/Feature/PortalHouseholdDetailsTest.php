<?php

namespace Tests\Feature;

use App\Http\Livewire\Portal\HouseholdProperty\Modals\AddHouseholdModal;
use App\Http\Livewire\Portal\HouseholdProperty\Modals\EditHouseholdModal;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The four facts a homeowner keeps about their own household — how the unit is
 * held, how the household is composed, what it earns, and where it lived
 * before — edited from the portal.
 *
 * The write goes through the same UpdateHouseholdFacts the office writes through,
 * so these follow it from the dialog that saves the record to the record
 * itself, and pin the ownership boundary: a household is only ever reached
 * through the signed-in account.
 */
class PortalHouseholdDetailsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    private $homeowner;

    /** @var \App\Models\Household\Household */
    private $household;

    protected function setUp(): void
    {
        parent::setUp();

        $address = Address::create([
            'label'       => 'TH 1',
            'subdivision' => 'Tradition Homes Ph-1',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'zip_code'    => '4107',
        ]);

        $property = Property::create([
            'block' => 4, 'lot' => 12, 'address_id' => $address->id, 'sqm' => 120, 'type' => 'house',
        ]);

        $head = Resident::create([
            'surname'                => 'Dela Cruz',
            'first_name'             => 'Juan',
            'birth_date'             => '1990-01-01',
            'sex'                    => 'male',
            'civil_status'           => 'married',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        $this->homeowner = factory(User::class)->create([
            'resident_id' => $head->id,
            'email'       => 'juan.delacruz@example.test',
        ]);

        $this->household = Household::create([
            'property_id'      => $property->id,
            'type'             => 'owner',
        ]);

        $this->household->placeHead($head->id, 'father');
    }

    // =====================================================================
    // The page
    // =====================================================================

    /**
     * The page reads unit first, then the household, then its members, and
     * offers the homeowner the Edit Details trigger.
     */
    public function test_the_household_page_shows_the_details_and_the_edit_trigger(): void
    {
        $response = $this->actingAs($this->homeowner, 'homeowner')
            ->get(route('portal.household-property'));

        $response->assertOk();
        // The page reads top to bottom: the unit, then the household, then its members.
        $response->assertSeeInOrder(['My Unit', 'Household Details', 'Members']);
        $response->assertSee('id="editHouseholdModal"', false);
        $response->assertSee('data-target="#editHouseholdModal"', false);
    }

    // =====================================================================
    // The dialog
    // =====================================================================

    /**
     * Opening the dialog loads the signed-in account's own household facts.
     */
    public function test_the_edit_dialog_loads_the_accounts_household_facts(): void
    {
        $this->household->update([
            'type'         => 'tenant',
            'prev_address' => '12 Mabini Street, Cavite',
        ]);

        Livewire::actingAs($this->homeowner, 'homeowner')
            ->test(EditHouseholdModal::class)
            ->assertSee('data-modal-skeleton class=', false)
            ->assertSee('data-modal-content class=', false)
            ->call('open')
            ->assertSet('loaded', true)
            ->assertSet('form.type', 'tenant')
            ->assertSet('form.prev_address', '12 Mabini Street, Cavite');
    }

    /**
     * Saving the four facts writes them on the account's household.
     */
    public function test_the_homeowner_can_save_the_four_facts(): void
    {
        Livewire::actingAs($this->homeowner, 'homeowner')
            ->test(EditHouseholdModal::class)
            ->call('open')
            ->set('form.type', 'tenant')
            ->set('form.prev_address', '45 Aguinaldo Highway, Cavite')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('household-details-updated');

        $household = $this->household->fresh();

        $this->assertSame('tenant', $household->type);
        $this->assertSame('45 Aguinaldo Highway, Cavite', $household->prev_address);
    }

    /**
     * A fact the household leaves empty is stored blank, not an empty string
     * the enum and the decimal columns would refuse.
     */
    public function test_an_unrecorded_fact_is_stored_blank(): void
    {
        $this->household->update([
            'prev_address' => 'Somewhere',
        ]);

        Livewire::actingAs($this->homeowner, 'homeowner')
            ->test(EditHouseholdModal::class)
            ->call('open')
            ->set('form.prev_address', '')
            ->call('save')
            ->assertHasNoErrors();

        $household = $this->household->fresh();

        $this->assertNull($household->prev_address);
    }

    /**
     * The record is never reached by id: an account with no household has
     * nothing to open.
     */
    public function test_an_account_without_a_household_cannot_open_the_dialog(): void
    {
        $stranger = factory(User::class)->create();

        $this->withoutExceptionHandling();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        Livewire::actingAs($stranger, 'homeowner')
            ->test(EditHouseholdModal::class)
            ->call('open');
    }

    // =====================================================================
    // Creating a household the office has not recorded
    // =====================================================================

    /**
     * A homeowner with no household creates one at an empty unit and becomes
     * its head; the portal never links them to somebody else's household.
     */
    public function test_a_homeowner_without_a_household_creates_one_as_its_head(): void
    {
        $unit = $this->emptyUnit(9, 9);
        $user = $this->accountWithoutHousehold('Nieves', 'Cora');

        Livewire::actingAs($user, 'homeowner')
            ->test(AddHouseholdModal::class)
            ->call('open')
            ->set('form.block', '9')
            ->set('form.lot', '9')
            ->set('form.type', 'owner')
            ->set('form.relationship', 'father')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('household-details-updated');

        $household = Household::where('property_id', $unit->id)->firstOrFail();

        $this->assertSame((int) $user->resident_id, (int) $household->head_resident?->id);
        $this->assertSame($household->id, (int) $user->fresh()->household?->id);
    }

    /**
     * A unit somebody already lives in is refused: the office places the
     * homeowner, and the household's head is never replaced.
     */
    public function test_a_homeowner_cannot_join_an_occupied_unit_from_the_portal(): void
    {
        $user = $this->accountWithoutHousehold('Nieves', 'Cora');

        Livewire::actingAs($user, 'homeowner')
            ->test(AddHouseholdModal::class)
            ->call('open')
            ->set('form.block', '4')
            ->set('form.lot', '12')
            ->set('form.type', 'owner')
            ->set('form.relationship', 'father')
            ->call('save')
            ->assertHasErrors(['form.block']);

        $this->assertSame(1, Household::count());
        $this->assertNull($user->fresh()->household);
    }

    private function emptyUnit(int $block, int $lot): Property
    {
        $address = Address::create([
            'label'       => 'TH 1 Blk ' . $block . ' Lot ' . $lot,
            'subdivision' => 'Tradition Homes Ph-1',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'zip_code'    => '4107',
        ]);

        return Property::create([
            'block' => $block, 'lot' => $lot, 'address_id' => $address->id, 'sqm' => 100, 'type' => 'house',
        ]);
    }

    private function accountWithoutHousehold(string $surname, string $firstName): User
    {
        $resident = Resident::create([
            'surname'                => $surname,
            'first_name'             => $firstName,
            'birth_date'             => '1992-02-02',
            'sex'                    => 'female',
            'civil_status'           => 'single',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        return factory(User::class)->create(['resident_id' => $resident->id]);
    }
}
