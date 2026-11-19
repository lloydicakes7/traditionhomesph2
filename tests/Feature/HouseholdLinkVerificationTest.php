<?php

namespace Tests\Feature;

use App\Actions\Homeowners\Profiles\SubmitProfileVerification;
use App\Enums\ProfileStatus;
use App\Http\Livewire\Admin\Homeowners\Verification\Modals\ProfileVerificationViewModal;
use App\Http\Livewire\Portal\HouseholdProperty\HouseholdLinkRequestList;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdLinkRequest;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Notifications\HouseholdLinkDecided;
use App\Notifications\HouseholdLinkRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HouseholdLinkVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_joining_an_occupied_unit_waits_for_the_household_head(): void
    {
        Notification::fake();

        [$household, $head] = $this->occupiedHousehold();
        $user = $this->joiningResident($household);

        $this->assertSame(0, HouseholdMember::where('resident_id', $user->resident_id)->count());

        Notification::assertSentTo($head, HouseholdLinkRequested::class, function (HouseholdLinkRequested $notification) use ($head) {
            return in_array('mail', $notification->via($head), true);
        });
    }

    public function test_the_head_approves_the_resident_and_the_office_is_told(): void
    {
        Notification::fake();

        [$household, $head] = $this->occupiedHousehold();
        $officer = $this->officer();
        $user = $this->joiningResident($household);
        $link = HouseholdLinkRequest::firstOrFail();

        $this->actingAs($head, 'homeowner')
            ->get(route('portal.household-requests'))
            ->assertOk()
            ->assertSee($user->resident->full_name);

        Livewire::actingAs($head, 'homeowner')
            ->test(HouseholdLinkRequestList::class)
            ->call('approve', $link->id)
            ->assertHasNoErrors();

        $member = HouseholdMember::where('resident_id', $user->resident_id)->firstOrFail();

        $this->assertFalse($member->is_head);
        $this->assertSame('son', $member->relationship);
        $this->assertSame('approved', HouseholdLinkRequest::firstOrFail()->status->value);

        Notification::assertSentTo($officer, HouseholdLinkDecided::class);
    }

    public function test_the_head_rejects_the_resident_and_no_member_is_written(): void
    {
        Notification::fake();

        [$household, $head] = $this->occupiedHousehold();
        $officer = $this->officer();
        $user = $this->joiningResident($household);
        $link = HouseholdLinkRequest::firstOrFail();

        Livewire::actingAs($head, 'homeowner')
            ->test(HouseholdLinkRequestList::class)
            ->call('reject', $link->id)
            ->assertHasNoErrors();

        $this->assertSame(0, HouseholdMember::where('resident_id', $user->resident_id)->count());
        $this->assertSame('rejected', HouseholdLinkRequest::firstOrFail()->status->value);

        Notification::assertSentTo($officer, HouseholdLinkDecided::class);
    }

    public function test_the_office_reads_the_heads_answer_in_the_review_dialog(): void
    {
        [$household, $head] = $this->occupiedHousehold();
        $user = $this->joiningResident($household);
        $link = HouseholdLinkRequest::firstOrFail();

        Livewire::actingAs($head, 'homeowner')
            ->test(HouseholdLinkRequestList::class)
            ->call('approve', $link->id);

        Livewire::actingAs($this->officer(), 'web')
            ->test(ProfileVerificationViewModal::class)
            ->call('load', $user->id)
            ->assertSee('Approved by the household head');
    }

    public function test_a_member_who_is_not_the_head_cannot_open_the_requests(): void
    {
        [$household, $head] = $this->occupiedHousehold();
        $this->joiningResident($household);

        $member = factory(Resident::class)->create(['surname' => 'Santos', 'first_name' => 'Ana']);
        $household->memberRecords()->create([
            'resident_id'  => $member->id,
            'relationship' => 'daughter',
            'is_head'      => false,
        ]);

        $account = User::create([
            'resident_id'       => $member->id,
            'email'             => 'ana@example.com',
            'password'          => bcrypt('ChosenPass1!'),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($account, 'homeowner')
            ->get(route('portal.household-requests'))
            ->assertForbidden();
    }

    public function test_asking_to_take_over_a_headed_household_waits_for_the_head(): void
    {
        Notification::fake();

        [$household, $head] = $this->occupiedHousehold();
        $user = $this->joiningResident($household, true);

        $link = HouseholdLinkRequest::firstOrFail();

        $this->assertTrue($link->wantsHead());
        $this->assertSame((int) $head->resident_id, (int) $household->fresh()->head_resident->id);
        $this->assertSame(0, HouseholdMember::where('resident_id', $user->resident_id)->count());

        Notification::assertSentTo($head, HouseholdLinkRequested::class);
    }

    public function test_the_head_approves_the_takeover(): void
    {
        Notification::fake();

        [$household, $head] = $this->occupiedHousehold();
        $user = $this->joiningResident($household, true);
        $link = HouseholdLinkRequest::firstOrFail();

        Livewire::actingAs($head, 'homeowner')
            ->test(HouseholdLinkRequestList::class)
            ->call('approve', $link->id)
            ->assertHasNoErrors();

        $this->assertSame((int) $user->resident_id, (int) $household->fresh()->head_resident->id);
        $this->assertSame(1, $household->memberRecords()->where('is_head', true)->count());
    }

    public function test_the_office_cannot_approve_a_profile_while_the_head_has_not_answered(): void
    {
        Notification::fake();

        [$household] = $this->occupiedHousehold();
        $user = $this->joiningResident($household);

        Livewire::actingAs($this->officer(), 'web')
            ->test(ProfileVerificationViewModal::class)
            ->call('load', $user->id)
            ->call('approve')
            ->assertSee(__('users.verification.awaiting_head'));

        $this->assertSame('submitted', $user->fresh()->profile_status->value);
    }

    private function joiningResident(Household $household, bool $beHead = false): User
    {
        Storage::fake('local');

        $resident = factory(Resident::class)->create([
            'surname'      => 'Dela Cruz',
            'first_name'   => 'Juan',
            'birth_date'   => '1990-05-12',
            'sex'          => 'male',
            'civil_status' => 'single',
            'mobile_no'    => '09171234567',
        ]);

        $user = User::create([
            'resident_id'       => $resident->id,
            'email'             => 'resident@example.com',
            'password'          => bcrypt('ChosenPass1!'),
            'is_active'         => true,
            'email_verified_at' => now(),
            'profile_status'    => ProfileStatus::INCOMPLETE,
        ]);

        app(SubmitProfileVerification::class)->handle($user, [
            'form' => [
                'philsys_card_no'        => '',
                'surname'                => 'Dela Cruz',
                'first_name'             => 'Juan',
                'middle_name'            => '',
                'suffix'                 => '',
                'birth_date'             => '1990-05-12',
                'birth_place'            => 'Trece Martires City',
                'sex'                    => 'male',
                'civil_status'           => 'single',
                'spouse_name'            => '',
                'citizenship'            => 'Filipino',
                'religion'               => '',
                'occupation'             => '',
                'mobile_no'              => '09171234567',
                'educational_attainment' => '',
                'sectors'                => [],
            ],
            'property'  => ['block' => '9', 'lot' => '3'],
            'household' => ['type' => 'owner', 'prev_address' => '', 'relationship' => 'son', 'be_head' => $beHead],
        ], UploadedFile::fake()->image('id.jpg'));

        return $user->fresh();
    }

    private function occupiedHousehold(): array
    {
        $address = Address::create([
            'label'       => 'Association address',
            'street'      => 'Mabini Street',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
            'is_primary'  => true,
        ]);

        $unit = Property::create([
            'block'      => 9,
            'lot'        => 3,
            'address_id' => $address->id,
            'sqm'        => 90,
            'type'       => 'townhouse',
        ]);

        $household = Household::create(['property_id' => $unit->id, 'type' => 'owner']);

        $resident = factory(Resident::class)->create(['surname' => 'Santos', 'first_name' => 'Rosa']);
        $household->placeHead($resident->id, 'mother');

        $head = User::create([
            'resident_id'       => $resident->id,
            'email'             => 'rosa@example.com',
            'password'          => bcrypt('ChosenPass1!'),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return [$household, $head];
    }

    private function officer(): Admin
    {
        $role = Role::create([
            'name'        => 'Verifier',
            'permissions' => ['homeowners.edit'],
        ]);

        return Admin::create([
            'name'      => 'Officer',
            'email'     => 'officer@example.com',
            'password'  => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }
}
