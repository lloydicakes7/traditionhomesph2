<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Homeowners\Homeowner\Modals\HomeownerFormModal;
use App\Http\Livewire\Admin\Homeowners\Household\Modals\HouseholdFormModal;
use App\Http\Livewire\Admin\Homeowners\Resident\Modals\ResidentFormModal;
use App\Http\Livewire\Admin\Modals\EditMembersModal;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RbiProfileFieldsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Property */
    private $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInAdmin();

        $address = Address::create([
            'label' => 'TH 2',
            'subdivision' => 'Tradition Homes Ph-2',
            'barangay' => 'Inocencio',
            'city' => 'Trece Martires City',
            'province' => 'Cavite',
            'region' => 'Region IV-A (CALABARZON)',
            'zip_code' => '4107',
            'is_primary' => true,
        ]);

        $this->unit = Property::create([
            'block' => 8,
            'lot' => 5,
            'address_id' => $address->id,
            'sqm' => 100,
            'type' => 'house',
        ]);
    }

    // =====================================================================
    // Resident form
    // =====================================================================

    public function test_the_resident_form_stores_the_rbi_profile(): void
    {
        $head = Resident::create([
            'surname' => 'Cruz',
            'first_name' => 'Ana',
            'birth_date' => '1980-01-01',
            'sex' => 'female',
            'civil_status' => 'single',
            'citizenship' => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        $household = Household::create([
            'property_id' => $this->unit->id,
            'type' => 'owner',
        ]);

        $household->placeHead($head->id, 'mother');

        Livewire::test(ResidentFormModal::class)
            ->call('openCreate')
            ->set('form.surname', 'Dela Cruz')
            ->set('form.first_name', 'Juan')
            ->set('form.middle_name', 'Santos')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '1985-06-14')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'married')
            ->set('form.educational_attainment', 'college_graduate')
            ->set('form.philsys_card_no', '1234-5678-9101-1213')
            ->set('form.sectors', ['pwd', 'indigenous_people'])
            ->set('property.block', (string) $this->unit->block)
            ->set('property.lot', (string) $this->unit->lot)
            ->set('household.relationship', 'son')
            ->call('save')
            ->assertHasNoErrors();

        $resident = Resident::where('first_name', 'JUAN')->firstOrFail();

        $this->assertSame('1234-5678-9101-1213', $resident->philsys_card_no);

        $this->assertSame(['pwd', 'indigenous_people'], $resident->sectorValues());
        $this->assertSame($household->id, (int) $resident->household->id);
    }

    public function test_the_resident_form_updates_the_profile(): void
    {
        $resident = Resident::create([
            'surname' => 'Reyes',
            'first_name' => 'Liza',
            'birth_date' => '1990-01-01',
            'sex' => 'female',
            'civil_status' => 'single',
            'citizenship' => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        $resident->update(['sectors' => ['unemployed']]);

        Livewire::test(ResidentFormModal::class)
            ->call('load', $resident->id)
            ->assertSet('form.sectors', ['unemployed'])
            ->set('form.sectors', ['ofw'])
            ->set('form.occupation', 'Seafarer')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['ofw', 'labor_force_employed'], $resident->fresh()->sectorValues());
    }

    public function test_a_philsys_number_that_is_not_sixteen_digits_is_refused(): void
    {
        Livewire::test(ResidentFormModal::class)
            ->call('openCreate')
            ->set('form.surname', 'Dela Cruz')
            ->set('form.first_name', 'Juan')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '1985-06-14')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'married')
            ->set('form.educational_attainment', 'college_graduate')
            ->set('form.philsys_card_no', '12345')
            ->call('save')
            ->assertHasErrors(['form.philsys_card_no']);

        $this->assertSame(0, Resident::count());
    }

    public function test_a_resident_below_eighteen_cannot_head_the_household(): void
    {
        Livewire::test(ResidentFormModal::class)
            ->call('openCreate')
            ->set('form.surname', 'Minor')
            ->set('form.first_name', 'Nino')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '2010-01-01')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'single')
            ->set('property.block', '11')
            ->set('property.lot', '4')
            ->set('property.sqm', '88.50')
            ->set('property.type', 'duplex')
            ->call('save')
            ->assertHasErrors(['form.birth_date']);

        $this->assertSame(0, Resident::where('first_name', 'NINO')->count());
    }

    public function test_a_minor_solo_parent_is_allowed_and_every_rule_violation_is_a_warning(): void
    {
        $service = app(\App\Services\Homeowners\SectoralTagService::class);
        $facts = [
            'age'                      => 16,
            'occupation'               => '',
            'civil_status'             => 'single',
            'educational_attainment'   => null,
            'solo_parent_special_case' => false,
        ];

        $this->assertSame([], $service->errors(['solo_parent'], $facts));
        $this->assertContains(
            'Solo parent below 18: allowed as a minor parent and flagged for the office.',
            $service->warnings(['solo_parent'], $facts)
        );
        $this->assertContains(
            'Occupation is required for Labor Force / Employed.',
            $service->warnings(['labor_force_employed'], $facts)
        );
    }

    public function test_a_sectoral_tag_must_agree_with_the_residents_age(): void
    {
        Livewire::test(ResidentFormModal::class)
            ->call('openCreate')
            ->set('form.surname', 'Cruz')
            ->set('form.first_name', 'Ana')
            ->set('form.sex', 'female')
            ->set('form.birth_date', '1985-06-14')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'single')
            ->set('form.sectors', ['senior_citizen'])
            ->assertSee('Senior Citizen (SC) requires age 60 or above.')
            ->set('property.block', '11')
            ->set('property.lot', '4')
            ->set('property.sqm', '88.50')
            ->set('property.type', 'duplex')
            ->call('save')
            ->assertHasErrors(['form.sectors'])
            ->assertSee('Senior Citizen (SC) requires age 60 or above.');

        $this->assertSame(0, Resident::where('first_name', 'ANA')->count());
    }

    // =====================================================================
    // Household form
    // =====================================================================

    public function test_the_household_form_stores_the_rbi_household_record(): void
    {
        $user = $this->homeownerAccount();

        Livewire::test(HouseholdFormModal::class)
            ->call('openCreate')
            ->set('form.head_resident_id', (string) $user->resident_id)
            ->set('form.relationship', 'father')
            ->set('form.property_id', (string) $this->unit->id)
            ->set('form.type', 'owner')
            ->call('save')
            ->assertHasNoErrors();

        $household = Household::where('property_id', $this->unit->id)->firstOrFail();

        $this->assertSame((int) $user->resident_id, (int) $household->head_resident?->id);
        $this->assertSame('Cruz Family', $household->display_name);
        $this->assertSame(1, (int) $household->families_count);
    }

    // =====================================================================
    // Add homeowner dialog
    // =====================================================================

    public function test_the_add_homeowner_dialog_asks_for_the_account_and_the_profile_in_one_step(): void
    {
        Livewire::test(HomeownerFormModal::class)
            ->call('openCreate')
            ->assertSee('Account')
            ->assertSee('Account activated')
            ->assertSee('PhilSys Card No.')
            ->assertSee('Surname *')
            ->assertDontSee('Household Information')
            ->assertDontSee('Household Members');
    }

    public function test_the_add_homeowner_dialog_stores_the_profile_and_the_account(): void
    {
        Livewire::test(HomeownerFormModal::class)
            ->call('openCreate')
            ->set('form.email', 'wizard@example.test')
            ->set('form.is_active', true)
            ->set('form.surname', 'Santos')
            ->set('form.first_name', 'Mario')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '1980-03-03')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'married')
            ->set('form.educational_attainment', 'high_school_graduate')
            ->set('form.sectors', ['labor_force_employed'])
            ->set('form.occupation', 'Teacher')
            ->set('property.block', (string) $this->unit->block)
            ->set('property.lot', (string) $this->unit->lot)
            ->call('save')
            ->assertHasNoErrors();

        $resident = Resident::where('first_name', 'MARIO')->firstOrFail();

        $this->assertSame(['labor_force_employed'], $resident->sectorValues());
        $this->assertSame('wizard@example.test', $resident->user->email);
        $this->assertFalse($resident->user->isPendingRegistration());

        $household = Household::where('property_id', $this->unit->id)->firstOrFail();

        $this->assertSame($resident->id, (int) $household->head_resident?->id);
    }

    public function test_the_homeowner_dialog_creates_a_new_unit_with_its_floor_area_and_type(): void
    {
        Livewire::test(HomeownerFormModal::class)
            ->call('openCreate')
            ->set('form.email', 'new.unit@example.test')
            ->set('form.surname', 'Bautista')
            ->set('form.first_name', 'Rico')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '1980-03-03')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'single')
            ->set('form.educational_attainment', 'college_graduate')
            ->set('property.block', '11')
            ->set('property.lot', '4')
            ->set('property.sqm', '88.50')
            ->set('property.type', 'duplex')
            ->call('save')
            ->assertHasNoErrors();

        $unit = Property::query()->atUnit(11, 4)->firstOrFail();

        $this->assertSame('88.50', $unit->sqm);
        $this->assertSame('duplex', $unit->type);
        $this->assertSame('RICO', $unit->currentHousehold->head_resident->first_name);
    }

    public function test_the_homeowner_dialog_asks_for_the_floor_area_of_a_new_unit(): void
    {
        Livewire::test(HomeownerFormModal::class)
            ->call('openCreate')
            ->set('form.email', 'no.area@example.test')
            ->set('form.surname', 'Bautista')
            ->set('form.first_name', 'Rico')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '1980-03-03')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'single')
            ->set('form.educational_attainment', 'college_graduate')
            ->set('property.block', '11')
            ->set('property.lot', '4')
            ->set('property.type', 'duplex')
            ->call('save')
            ->assertHasErrors(['property.sqm']);

        $this->assertSame(0, Property::query()->atUnit(11, 4)->count());
    }

    public function test_the_homeowner_dialog_keeps_the_household_on_file_while_editing(): void
    {
        $user = $this->homeownerAccount();
        $household = Household::create([
            'property_id' => $this->unit->id,
            'type'        => 'owner',
        ]);

        $household->placeHead($user->resident_id, 'mother');

        Livewire::test(HomeownerFormModal::class)
            ->call('load', $user->id)
            ->assertSet('property.block', (string) $this->unit->block)
            ->assertSee('Tenure')
            ->assertDontSee('This person heads the household')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($this->unit->id, (int) $household->fresh()->property_id);
    }

    public function test_the_resident_dialog_can_make_the_new_resident_the_household_head(): void
    {
        $head = $this->homeownerAccount();

        $household = Household::create([
            'property_id' => $this->unit->id,
            'type'        => 'owner',
        ]);

        $household->placeHead($head->resident_id, 'mother');

        Livewire::test(ResidentFormModal::class)
            ->call('openCreate')
            ->set('form.surname', 'Dela Cruz')
            ->set('form.first_name', 'Juan')
            ->set('form.sex', 'male')
            ->set('form.birth_date', '1985-06-14')
            ->set('form.citizenship', 'Filipino')
            ->set('form.civil_status', 'single')
            ->set('form.educational_attainment', 'college_graduate')
            ->set('property.block', (string) $this->unit->block)
            ->set('property.lot', (string) $this->unit->lot)
            ->set('household.relationship', 'father')
            ->set('household.be_head', true)
            ->call('save')
            ->assertHasNoErrors();

        $resident = Resident::where('first_name', 'JUAN')->firstOrFail();

        $this->assertSame((int) $resident->id, (int) $household->fresh()->head_resident->id);
    }

    // =====================================================================
    // Edit profile
    // =====================================================================

    public function test_the_edit_profile_dialog_saves_the_profile(): void
    {
        $resident = $this->completeResident('Garcia', 'Ramon');
        $user = $this->accountFor($resident);

        Livewire::test(HomeownerFormModal::class)
            ->call('load', $user->id)
            ->assertSet('form.philsys_card_no', '')
            ->set('form.philsys_card_no', '9999-8888-7777-6666')
            ->set('form.sectors', ['pwd'])
            ->call('save')
            ->assertHasNoErrors();

        $resident = $resident->fresh();

        $this->assertSame('9999-8888-7777-6666', $resident->philsys_card_no);
        $this->assertSame(['pwd'], $resident->sectorValues());
    }

    public function test_the_edit_profile_dialog_refuses_a_bad_philsys_number(): void
    {
        $resident = $this->completeResident('Mendoza', 'Lito');
        $user = $this->accountFor($resident);

        Livewire::test(HomeownerFormModal::class)
            ->call('load', $user->id)
            ->set('form.philsys_card_no', '12345')
            ->call('save')
            ->assertHasErrors(['form.philsys_card_no']);

        $this->assertNull($resident->fresh()->philsys_card_no);
    }

    public function test_the_spouse_field_is_enabled_only_for_a_married_applicant(): void
    {
        $resident = $this->completeResident('Santos', 'Mario');
        $resident->forceFill(['civil_status' => 'married', 'spouse_name' => 'Rosa Santos'])->save();
        $user = $this->accountFor($resident);

        $component = Livewire::test(HomeownerFormModal::class)
            ->call('load', $user->id)
            ->assertSee('Spouse Name');

        $this->assertFalse($this->spouseFieldIsDisabled($component->lastRenderedDom));

        $component->set('form.civil_status', 'single');

        $this->assertTrue($this->spouseFieldIsDisabled($component->lastRenderedDom));

        $component->set('form.civil_status', 'married')->call('save')->assertHasNoErrors();

        $this->assertSame('ROSA SANTOS', $resident->fresh()->spouse_name);
    }

    public function test_a_spouse_name_is_dropped_once_the_status_is_no_longer_married(): void
    {
        $resident = $this->completeResident('Cruz', 'Ana');
        $resident->forceFill(['civil_status' => 'married', 'spouse_name' => 'Ben Cruz'])->save();
        $user = $this->accountFor($resident);

        Livewire::test(HomeownerFormModal::class)
            ->call('load', $user->id)
            ->set('form.civil_status', 'widowed')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($resident->fresh()->spouse_name);
    }

    public function test_the_resident_edit_dialog_enables_the_spouse_field_only_when_married(): void
    {
        $resident = $this->completeResident('Reyes', 'Liza');
        $resident->forceFill(['civil_status' => 'single'])->save();

        $component = Livewire::test(ResidentFormModal::class)
            ->call('load', $resident->id)
            ->assertSee('Spouse Name');

        $this->assertTrue($this->spouseFieldIsDisabled($component->lastRenderedDom));

        $component->set('form.civil_status', 'married');

        $this->assertFalse($this->spouseFieldIsDisabled($component->lastRenderedDom));
    }

    // =====================================================================
    // Members dialog
    // =====================================================================

    public function test_the_members_dialog_stores_a_new_residents_profile(): void
    {
        $head = $this->homeownerAccount();

        $household = Household::create([
            'property_id'      => $this->unit->id,
            'type'             => 'owner',
        ]);

        $household->placeHead($head->resident_id, 'mother');

        Livewire::test(EditMembersModal::class)
            ->call('load', $household->id)
            ->set('memberType', 'new_resident')
            ->set('memberForm.surname', 'Bautista')
            ->set('memberForm.first_name', 'Rosa')
            ->set('memberForm.sex', 'female')
            ->set('memberForm.birth_date', '2010-02-02')
            ->set('memberForm.citizenship', 'Filipino')
            ->set('memberForm.civil_status', 'single')
            ->set('memberForm.educational_attainment', 'high_school_level')
            ->set('memberForm.relationship', 'daughter')
            ->set('memberForm.philsys_card_no', '1111-2222-3333-4444')
            ->set('memberForm.sectors', ['osy'])
            ->call('addMember')
            ->assertHasNoErrors();

        $resident = Resident::where('first_name', 'ROSA')->firstOrFail();

        $this->assertSame('1111-2222-3333-4444', $resident->philsys_card_no);
        $this->assertSame(['osy'], $resident->sectorValues());
    }

    public function test_a_member_added_to_the_list_shows_its_relationship_at_once(): void
    {
        $head = $this->homeownerAccount();

        $household = Household::create([
            'property_id'      => $this->unit->id,
            'type'             => 'owner',
        ]);

        $household->placeHead($head->resident_id, 'mother');

        Livewire::test(EditMembersModal::class)
            ->call('load', $household->id)
            ->set('memberType', 'new_resident')
            ->set('memberForm.surname', 'Bautista')
            ->set('memberForm.first_name', 'Rosa')
            ->set('memberForm.sex', 'female')
            ->set('memberForm.birth_date', '2010-02-02')
            ->set('memberForm.citizenship', 'Filipino')
            ->set('memberForm.civil_status', 'single')
            ->set('memberForm.educational_attainment', 'high_school_level')
            ->set('memberForm.relationship', 'daughter')
            ->call('addMember')
            ->assertSet('members.1.relationship_label', 'Daughter');
    }

    public function test_a_members_details_are_edited_inside_the_members_dialog(): void
    {
        $head = $this->homeownerAccount();

        $household = Household::create([
            'property_id'      => $this->unit->id,
            'type'             => 'owner',
        ]);

        $household->placeHead($head->resident_id, 'mother');

        Livewire::test(EditMembersModal::class)
            ->call('load', $household->id)
            ->call('startEditMember', 0)
            ->assertSet('editingMemberIndex', 0)
            ->set('memberForm.relationship', 'Aunt')
            ->call('saveMemberEdit')
            ->assertHasNoErrors()
            ->assertSet('editingMemberIndex', null)
            ->assertSet('members.0.relationship_label', 'Aunt');

        $member = $household->memberRecords()->firstOrFail();

        $this->assertSame('AUNT', $member->relationship);
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

    private function spouseFieldIsDisabled(string $html): bool
    {
        return (bool) preg_match('/name="form\.spouse_name"[^>]*\sdisabled/', $html);
    }

    private function completeResident(string $surname, string $firstName): Resident
    {
        return Resident::create([
            'surname'                => $surname,
            'first_name'             => $firstName,
            'birth_date'             => '1985-04-04',
            'sex'                    => 'male',
            'civil_status'           => 'married',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);
    }

    private function accountFor(Resident $resident): User
    {
        return User::create([
            'resident_id' => $resident->id,
            'email' => strtolower($resident->first_name) . '.' . strtolower($resident->surname) . '@example.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
    }

    private function homeownerAccount(): User
    {
        $resident = Resident::create([
            'surname' => 'Cruz',
            'first_name' => 'Ana',
            'birth_date' => '1992-05-05',
            'sex' => 'female',
            'civil_status' => 'single',
            'citizenship' => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        return User::create([
            'resident_id' => $resident->id,
            'email' => 'ana.cruz@example.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
    }

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name' => 'Maria Clara Villanueva',
            'email' => 'rbi-profile-' . uniqid() . '@example.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
            'role_id' => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
