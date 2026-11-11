<?php

namespace Tests\Feature\Actions\Homeowners;

use App\Actions\Homeowners\Addresses\CreateAddress;
use App\Actions\Homeowners\Households\CreateHousehold;
use App\Actions\Homeowners\Households\DeleteHouseholds;
use App\Actions\Homeowners\Households\SaveHouseholdMembers;
use App\Actions\Homeowners\Residents\CreateResident;
use App\Actions\Homeowners\Residents\DeleteResidents;
use App\Actions\Homeowners\Residents\FirstOrCreateResident;
use App\Actions\Homeowners\Users\ToggleUserAccount;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomeownerActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Addresses =====

    public function test_a_new_primary_address_replaces_the_old_one(): void
    {
        $admin = $this->admin();
        $this->address('Old', true);

        $address = app(CreateAddress::class)->handle([
            'label'       => 'New',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'San Isidro',
            'city'        => 'Cabuyao',
            'province'    => 'Laguna',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
            'is_primary'  => true,
        ], $admin);

        $this->assertTrue($address->is_primary);
        $this->assertSame(1, Address::where('is_primary', true)->count());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'address',
        ]);
    }

    // ===== Residents =====

    public function test_creating_a_resident_stores_the_profile_and_audits_it(): void
    {
        $admin = $this->admin();

        $resident = app(CreateResident::class)->handle([
            'surname'                => 'Dela Cruz',
            'first_name'             => 'Juan',
            'sex'                    => 'male',
            'birth_date'             => '1985-06-14',
            'civil_status'           => 'married',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
            'sectors'                => ['pwd'],
        ], null, $admin);

        $this->assertSame('Dela Cruz', $resident->surname);
        $this->assertSame(['pwd'], $resident->sectorValues());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'homeowner',
        ]);
    }

    public function test_the_same_person_is_matched_rather_than_duplicated(): void
    {
        $action = app(FirstOrCreateResident::class);

        $payload = [
            'surname'                => 'Santos',
            'first_name'             => 'Ana',
            'middle_name'            => 'Reyes',
            'birth_date'             => '1990-01-01',
            'sex'                    => 'female',
            'civil_status'           => 'single',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ];

        $first = $action->handle($payload);
        $second = $action->handle($payload);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Resident::where('surname', 'Santos')->count());
    }

    public function test_deleting_residents_unlinks_their_accounts(): void
    {
        $admin = $this->admin();
        $resident = factory(Resident::class)->create(['surname' => 'Santos']);
        $user = factory(User::class)->create(['resident_id' => $resident->id]);

        app(DeleteResidents::class)->handle([$resident->id], $admin);

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
        $this->assertNull($user->fresh()->resident_id);
    }

    // ===== Households =====

    public function test_a_household_cannot_take_a_unit_another_household_holds(): void
    {
        $admin = $this->admin();
        $address = $this->address('Subdivision');
        $property = $this->property($address, 1, 1);
        $owner = $this->resident('Santos');
        $other = $this->resident('Dela Cruz');

        app(CreateHousehold::class)->handle($this->householdPayload($owner->id, $property->id), $admin);

        $this->expectException(\RuntimeException::class);

        app(CreateHousehold::class)->handle($this->householdPayload($other->id, $property->id), $admin);
    }

    public function test_a_resident_cannot_head_two_households(): void
    {
        $admin = $this->admin();
        $address = $this->address('Subdivision');
        $owner = $this->resident('Santos');

        app(CreateHousehold::class)->handle(
            $this->householdPayload($owner->id, $this->property($address, 1, 1)->id),
            $admin
        );

        $this->expectException(\RuntimeException::class);

        app(CreateHousehold::class)->handle(
            $this->householdPayload($owner->id, $this->property($address, 2, 2)->id),
            $admin
        );
    }

    public function test_saving_the_member_list_keeps_one_head(): void
    {
        $admin = $this->admin();
        $head = $this->resident('Santos');
        $member = $this->resident('Reyes');

        $household = Household::create([
            'property_id'      => $this->property($this->address('Subdivision'), 1, 1)->id,
            'type'             => 'owner',
        ]);

        app(SaveHouseholdMembers::class)->handle($household->id, [
            ['resident_id' => (string) $head->id, 'relationship' => 'father', 'is_head' => true],
            ['resident_id' => (string) $member->id, 'relationship' => 'son', 'is_head' => true],
        ], $admin);

        $this->assertSame(1, HouseholdMember::where('household_id', $household->id)->where('is_head', true)->count());
        $this->assertSame(
            [$head->id],
            HouseholdMember::where('household_id', $household->id)->where('is_head', true)
                ->pluck('resident_id')->map(function ($id) {
                    return (int) $id;
                })->all()
        );
    }

    public function test_deleting_a_household_removes_the_members_it_profiled(): void
    {
        $admin = $this->admin();
        $head = $this->resident('Santos');
        $member = $this->resident('Reyes');

        $household = Household::create([
            'property_id'      => $this->property($this->address('Subdivision'), 1, 1)->id,
            'type'             => 'owner',
        ]);

        $household->placeHead($head->id, 'father');

        HouseholdMember::create(['household_id' => $household->id, 'resident_id' => $member->id, 'relationship' => 'son']);

        app(DeleteHouseholds::class)->handle([$household->id], $admin);

        $this->assertDatabaseMissing('households', ['id' => $household->id]);
        $this->assertDatabaseMissing('household_members', ['household_id' => $household->id]);
        $this->assertDatabaseMissing('residents', ['id' => $member->id]);
        $this->assertDatabaseHas('residents', ['id' => $head->id]);
    }

    // ===== Accounts =====

    public function test_toggling_an_account_flips_its_active_flag(): void
    {
        $admin = $this->admin();
        $account = $this->account($this->resident('Santos'));

        $this->assertFalse(app(ToggleUserAccount::class)->handle($account->id, $admin));
        $this->assertFalse($account->fresh()->is_active);
    }

    // ===== Helpers =====

    private function householdPayload(int $residentId, int $propertyId): array
    {
        return [
            'head_resident_id' => $residentId,
            'property_id'      => $propertyId,
            'type'             => 'owner',
            'relationship'     => 'father',
        ];
    }

    private function resident(string $surname): Resident
    {
        return factory(Resident::class)->create(['surname' => $surname]);
    }

    private function account(Resident $resident): User
    {
        return factory(User::class)->create(['resident_id' => $resident->id]);
    }

    private function address(string $label): Address
    {
        return Address::create([
            'label'       => $label,
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'San Isidro',
            'city'        => 'Cabuyao',
            'province'    => 'Laguna',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
        ]);
    }

    private function property(Address $address, int $block, int $lot): Property
    {
        return Property::create([
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $address->id,
            'sqm'        => 100,
            'type'       => 'house',
        ]);
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'homeowners-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }
}
