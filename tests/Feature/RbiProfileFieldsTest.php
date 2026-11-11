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
            ->set('form.sectors', ['pwd', 'senior_citizen'])
            ->set('household.block', (string) $this->unit->block)
            ->set('household.lot', (string) $this->unit->lot)
            ->set('household.link_household', true)
            ->set('household.relationship', 'son')
            ->call('save')
            ->assertHasNoErrors();

        $resident = Resident::where('first_name', 'Juan')->firstOrFail();

        $this->assertSame('1234-5678-9101-1213', $resident->philsys_card_no);

        $this->assertSame(['pwd', 'senior_citizen'], $resident->sectorValues());
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
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(['ofw'], $resident->fresh()->sectorValues());
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
            ->set('household.block', (string) $this->unit->block)
            ->set('household.lot', (string) $this->unit->lot)
            ->call('save')
            ->assertHasNoErrors();

        $resident = Resident::where('first_name', 'Mario')->firstOrFail();

        $this->assertSame(['labor_force_employed'], $resident->sectorValues());
        $this->assertSame('wizard@example.test', $resident->user->email);
        $this->assertFalse($resident->user->isPendingRegistration());

        $household = Household::where('property_id', $this->unit->id)->firstOrFail();

        $this->assertSame($resident->id, (int) $household->head_resident?->id);
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

    public function test_the_spouse_field_shows_only_for_a_married_applicant(): void
    {
        $resident = $this->completeResident('Santos', 'Mario');
        $resident->forceFill(['civil_status' => 'married', 'spouse_name' => 'Rosa Santos'])->save();
        $user = $this->accountFor($resident);

        Livewire::test(HomeownerFormModal::class)
            ->call('load', $user->id)
            ->assertSee('Spouse Name')
            ->set('form.civil_status', 'single')
            ->assertDontSee('Spouse Name')
            ->set('form.civil_status', 'married')
            ->assertSee('Spouse Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Rosa Santos', $resident->fresh()->spouse_name);
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

    public function test_the_resident_edit_dialog_shows_the_spouse_field_only_when_married(): void
    {
        $resident = $this->completeResident('Reyes', 'Liza');
        $resident->forceFill(['civil_status' => 'single'])->save();

        Livewire::test(ResidentFormModal::class)
            ->call('load', $resident->id)
            ->assertDontSee('Spouse Name')
            ->set('form.civil_status', 'married')
            ->assertSee('Spouse Name');
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

        $resident = Resident::where('first_name', 'Rosa')->firstOrFail();

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

        $this->assertSame('Aunt', $member->relationship);
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

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
            'password_changed_at' => now(),
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
            'password_changed_at' => now(),
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
            'password_changed_at' => now(),
            'is_active' => true,
            'role_id' => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
