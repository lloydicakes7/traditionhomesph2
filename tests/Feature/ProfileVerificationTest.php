<?php

namespace Tests\Feature;

use App\Actions\Homeowners\Profiles\NotifyPendingProfileVerifications;
use App\Actions\Homeowners\Profiles\RejectProfile;
use App\Actions\Homeowners\Profiles\SubmitProfileVerification;
use App\Actions\Homeowners\Profiles\VerifyProfile;
use App\Enums\ProfileStatus;
use App\Http\Livewire\Portal\Onboarding\ProfileWizard;
use App\Http\Livewire\Portal\PersonalInformation\PersonalInformationForm;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Notifications\PendingProfileVerifications;
use App\Notifications\PendingProfileVerificationsDigest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The profile gate: an account gives its own details first, the office checks
 * them, and only then does the portal open.
 */
class ProfileVerificationTest extends TestCase
{
    use RefreshDatabase;

    // ===== The gate =====

    public function test_an_unverified_profile_sends_the_closed_pages_to_the_onboarding_form(): void
    {
        $user = $this->registered();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/requests')
            ->assertRedirect(route('portal.onboarding'))
            ->assertSessionHas('notify');

        $this->actingAs($user, 'homeowner')
            ->get('/portal/bookings')
            ->assertRedirect(route('portal.onboarding'));

        $this->actingAs($user, 'homeowner')
            ->get('/portal/household-property')
            ->assertRedirect(route('portal.onboarding'));
    }

    public function test_a_closed_page_tells_the_resident_why(): void
    {
        $user = $this->registered();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/requests')
            ->assertRedirect(route('portal.onboarding'));

        $this->get('/portal/onboarding')
            ->assertOk()
            ->assertSee(__('auth.onboarding.gate_notice'));
    }

    public function test_the_dashboard_statement_and_payments_open_before_verification(): void
    {
        $user = $this->registered();

        $this->actingAs($user, 'homeowner')->get('/portal')->assertOk();
        $this->actingAs($user, 'homeowner')->get('/portal/statement-of-account')->assertOk();
        $this->actingAs($user, 'homeowner')->get('/portal/payments')->assertOk();
    }

    public function test_the_onboarding_form_and_the_account_stay_open(): void
    {
        $user = $this->registered();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/onboarding')
            ->assertOk()
            ->assertSee('Complete your profile')
            ->assertSee('progress-bar bg-success', false)
            ->assertSee('collapse show', false)
            ->assertSee(__('auth.onboarding.available_title'))
            ->assertSee(__('auth.onboarding.locked_title'));

        $this->actingAs($user, 'homeowner')
            ->get('/portal/account-details')
            ->assertOk();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/notifications')
            ->assertOk();
    }

    public function test_a_submitted_profile_cannot_open_the_onboarding_form_again(): void
    {
        $user = $this->submitted();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/onboarding')
            ->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('notify');

        $this->get('/portal')
            ->assertOk()
            ->assertSee(__('auth.onboarding.under_review'));
    }

    public function test_a_submitted_profile_is_sent_back_from_a_closed_service(): void
    {
        $user = $this->submitted();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/requests')
            ->assertRedirect(route('portal.dashboard'))
            ->assertSessionHas('notify');
    }

    public function test_a_submitted_profile_cannot_submit_the_wizard_again(): void
    {
        $user = $this->submitted();
        $submittedAt = $user->profile_submitted_at;

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->call('submit')
            ->assertRedirect(route('portal.dashboard'));

        $this->assertSame($submittedAt->toDateTimeString(), $user->fresh()->profile_submitted_at->toDateTimeString());
    }

    public function test_the_valid_id_card_offers_the_camera_capture(): void
    {
        $user = $this->registered();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/onboarding')
            ->assertOk()
            ->assertSee('js/camera.js')
            ->assertSee('data-thha-camera-open', false);

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->call('open', 3)
            ->assertSee('id="thhaCameraModal"', false)
            ->assertSee('data-thha-id-input', false);
    }

    public function test_a_section_opens_from_its_card_header(): void
    {
        $user = $this->registered();

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->assertSet('step', 1)
            ->call('open', 3)
            ->assertSet('step', 3)
            ->call('open', 2)
            ->assertSet('step', 2);
    }

    // ===== Submission =====

    public function test_submitting_the_form_queues_the_profile_and_writes_the_household(): void
    {
        Storage::fake('local');

        $user = $this->registered();
        $address = $this->primaryAddress();

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->set('form.birth_place', 'Trece Martires City')
            ->set('form.citizenship', 'Filipino')
            ->set('form.mobile_no', '09171234567')
            ->set('property.block', '7')
            ->set('property.lot', '12')
            ->set('property.sqm', '120.50')
            ->set('property.type', 'house')
            ->set('household.type', 'owner')
            ->set('household.relationship', 'father')
            ->set('idFile', UploadedFile::fake()->image('id.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $user = $user->fresh();

        $this->assertSame(ProfileStatus::SUBMITTED, $user->profile_status->value);
        $this->assertNotNull($user->profile_submitted_at);
        $this->assertNotNull($user->resident->valid_id_path);
        Storage::disk('local')->assertExists($user->resident->valid_id_path);

        $unit = Property::first();

        $this->assertSame(1, Household::count());
        $this->assertSame($address->id, (int) $unit->address_id);
        $this->assertSame('120.50', $unit->sqm);
        $this->assertSame('house', $unit->type);
        $this->assertSame(1, Household::first()->memberRecords()->where('is_head', true)->count());
        $this->assertSame('father', Household::first()->headMember->relationship);
    }

    public function test_a_named_unit_that_is_not_on_file_asks_for_its_floor_area_and_type(): void
    {
        $user = $this->registered();
        $this->primaryAddress();

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->set('form.birth_place', 'Trece Martires City')
            ->set('form.citizenship', 'Filipino')
            ->set('property.block', '7')
            ->set('property.lot', '12')
            ->set('property.type', 'house')
            ->set('idFile', UploadedFile::fake()->image('id.jpg'))
            ->call('submit')
            ->assertHasErrors(['property.sqm']);

        $this->assertSame(0, Property::count());
    }

    public function test_a_unit_without_a_household_makes_the_resident_its_head(): void
    {
        $user = $this->registered();
        $address = $this->primaryAddress();

        Property::create([
            'block'      => 9,
            'lot'        => 3,
            'address_id' => $address->id,
            'sqm'        => 90,
            'type'       => 'townhouse',
        ]);

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->set('form.birth_place', 'Trece Martires City')
            ->set('form.citizenship', 'Filipino')
            ->set('property.block', '9')
            ->set('property.lot', '3')
            ->set('household.relationship', 'mother')
            ->set('idFile', UploadedFile::fake()->image('id.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $household = Household::firstOrFail();

        $this->assertSame((int) $user->resident_id, (int) $household->head_resident->id);
        $this->assertSame('mother', $household->headMember->relationship);
    }

    public function test_an_occupied_unit_joins_the_resident_as_a_member_unless_they_ask_to_head_it(): void
    {
        $user = $this->registered();
        $address = $this->primaryAddress();

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

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->set('form.birth_place', 'Trece Martires City')
            ->set('form.citizenship', 'Filipino')
            ->set('property.block', '9')
            ->set('property.lot', '3')
            ->set('household.relationship', 'son')
            ->set('idFile', UploadedFile::fake()->image('id.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $member = $household->memberRecords()->where('resident_id', $user->resident_id)->firstOrFail();

        $this->assertFalse($member->is_head);
        $this->assertSame('son', $member->relationship);
        $this->assertSame((int) $resident->id, (int) $household->fresh()->head_resident->id);
    }

    public function test_the_household_head_of_the_named_unit_is_told_so(): void
    {
        $user = $this->registered();
        $address = $this->primaryAddress();

        $unit = Property::create([
            'block'      => 9,
            'lot'        => 3,
            'address_id' => $address->id,
            'sqm'        => 90,
            'type'       => 'townhouse',
        ]);

        $household = Household::create(['property_id' => $unit->id, 'type' => 'owner']);
        $household->placeHead($user->resident_id, 'father');

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->assertSee(__('households.note.own_head'))
            ->assertDontSee(__('households.note.occupied'))
            ->assertDontSee(__('households.be_head'));
    }

    public function test_an_occupied_unit_lets_the_resident_take_over_as_head(): void
    {
        $user = $this->registered();
        $address = $this->primaryAddress();

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

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->set('form.birth_place', 'Trece Martires City')
            ->set('form.citizenship', 'Filipino')
            ->set('property.block', '9')
            ->set('property.lot', '3')
            ->set('household.relationship', 'father')
            ->set('household.be_head', true)
            ->set('idFile', UploadedFile::fake()->image('id.jpg'))
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame((int) $user->resident_id, (int) $household->fresh()->head_resident->id);
        $this->assertSame(1, $household->memberRecords()->where('is_head', true)->count());
    }

    public function test_the_verification_page_counts_accounts_by_profile_status(): void
    {
        $this->submitted();

        $this->actingAs($this->officer(), 'web')
            ->get(route('homeowners.profile-verifications'))
            ->assertOk()
            ->assertSee(ProfileStatus::SUBMITTED()->label())
            ->assertSee(ProfileStatus::VERIFIED()->label());
    }

    public function test_the_portal_dashboard_shows_the_account_status(): void
    {
        $user = $this->submitted();

        $this->actingAs($user, 'homeowner')
            ->get('/portal')
            ->assertOk()
            ->assertSee(ProfileStatus::SUBMITTED()->label())
            ->assertSee(__('auth.onboarding.under_review'));
    }

    public function test_a_submission_without_an_id_is_refused(): void
    {
        Storage::fake('local');

        $user = $this->registered();
        $this->primaryAddress();

        Livewire::actingAs($user, 'homeowner')
            ->test(ProfileWizard::class)
            ->set('form.birth_place', 'Trece Martires City')
            ->set('form.citizenship', 'Filipino')
            ->set('property.block', '7')
            ->set('property.lot', '12')
            ->set('property.sqm', '120')
            ->set('property.type', 'house')
            ->call('submit')
            ->assertHasErrors(['idFile']);

        $this->assertSame(ProfileStatus::INCOMPLETE, $user->fresh()->profile_status->value);
    }

    // ===== Decisions =====

    public function test_an_officer_verifies_the_profile_and_the_services_open(): void
    {
        $user = $this->submitted();

        app(VerifyProfile::class)->handle($user, $this->officer());

        $user = $user->fresh();

        $this->assertSame(ProfileStatus::VERIFIED, $user->profile_status->value);
        $this->assertNotNull($user->profile_verified_at);

        $this->actingAs($user, 'homeowner')
            ->get('/portal')
            ->assertOk();
    }

    public function test_an_officer_sends_the_profile_back_and_the_resident_reads_the_note(): void
    {
        $user = $this->submitted();

        app(RejectProfile::class)->handle($user, 'The block and lot do not match our records.', $this->officer());

        $user = $user->fresh();

        $this->assertSame(ProfileStatus::REJECTED, $user->profile_status->value);
        $this->assertSame('The block and lot do not match our records.', $user->profile_rejection_note);

        $this->actingAs($user, 'homeowner')
            ->get('/portal/onboarding')
            ->assertOk()
            ->assertSee('The block and lot do not match our records.');
    }

    public function test_a_decision_without_a_note_is_refused(): void
    {
        $user = $this->submitted();

        $this->expectException(\RuntimeException::class);

        app(RejectProfile::class)->handle($user, '   ', $this->officer());
    }

    // ===== The office's screen =====

    public function test_the_office_opens_the_queue_and_the_review_dialog(): void
    {
        $user = $this->submitted();
        $officer = $this->officer();

        $this->actingAs($officer, 'web')
            ->get(route('homeowners.profile-verifications'))
            ->assertOk()
            ->assertSee($user->email);

        Livewire::actingAs($officer, 'web')
            ->test(\App\Http\Livewire\Admin\Homeowners\Verification\Modals\ProfileVerificationViewModal::class)
            ->call('load', $user->id)
            ->assertSet('loaded', true)
            ->assertSee($user->resident->full_name)
            ->set('note', 'The block and lot do not match our records.')
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertSame(ProfileStatus::REJECTED, $user->fresh()->profile_status->value);
    }

    public function test_a_verified_profile_leaves_the_queue(): void
    {
        $user = $this->submitted();
        $officer = $this->officer();

        app(VerifyProfile::class)->handle($user, $this->officer());

        $this->actingAs($officer, 'web')
            ->get(route('homeowners.profile-verifications'))
            ->assertOk()
            ->assertDontSee($user->email);
    }

    public function test_the_review_dialog_previews_the_submitted_id(): void
    {
        $user = $this->submitted();

        Livewire::actingAs($this->officer(), 'web')
            ->test(\App\Http\Livewire\Admin\Homeowners\Verification\Modals\ProfileVerificationViewModal::class)
            ->call('load', $user->id)
            ->assertSee(route('homeowners.residents.valid-id', $user->resident), false);
    }

    public function test_an_officer_without_the_permission_cannot_open_the_queue(): void
    {
        $this->submitted();

        $role = Role::create(['name' => 'Front Desk', 'permissions' => []]);

        $officer = Admin::create([
            'name'      => 'Front Desk',
            'email'     => 'frontdesk@example.com',
            'password'  => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($officer, 'web')
            ->get(route('homeowners.profile-verifications'))
            ->assertForbidden();
    }

    // ===== An edit after verification =====

    public function test_editing_a_verified_profile_puts_it_back_in_the_queue(): void
    {
        $user = $this->submitted();

        app(VerifyProfile::class)->handle($user, $this->officer());

        Livewire::actingAs($user->fresh(), 'homeowner')
            ->test(PersonalInformationForm::class)
            ->set('form.surname', 'Cruz')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(ProfileStatus::SUBMITTED, $user->fresh()->profile_status->value);
        $this->assertSame('Cruz', $user->fresh()->resident->surname);
    }

    // ===== Reminders =====

    public function test_the_office_is_reminded_and_mailed_once_every_three_days(): void
    {
        Notification::fake();

        $this->submitted();
        $officer = $this->officer();

        app(NotifyPendingProfileVerifications::class)->handle();

        Notification::assertSentTo($officer, PendingProfileVerifications::class);
        Notification::assertSentToTimes($officer, PendingProfileVerificationsDigest::class, 1);

        app(NotifyPendingProfileVerifications::class)->handle();

        Notification::assertSentToTimes($officer, PendingProfileVerifications::class, 2);
        Notification::assertSentToTimes($officer, PendingProfileVerificationsDigest::class, 1);
    }

    public function test_the_reminder_skips_an_officer_without_the_permission(): void
    {
        Notification::fake();

        $this->submitted();

        $role = Role::create(['name' => 'Front Desk', 'permissions' => []]);

        $officer = Admin::create([
            'name'      => 'Front Desk',
            'email'     => 'frontdesk@example.com',
            'password'  => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        app(NotifyPendingProfileVerifications::class)->handle();

        Notification::assertNotSentTo($officer, PendingProfileVerifications::class);
        Notification::assertNotSentTo($officer, PendingProfileVerificationsDigest::class);
    }

    // ===== Fixtures =====

    private function registered(): User
    {
        $resident = factory(Resident::class)->create([
            'surname'      => 'Dela Cruz',
            'first_name'   => 'Juan',
            'middle_name'  => 'Santos',
            'birth_date'   => '1990-05-12',
            'sex'          => 'male',
            'civil_status' => 'single',
            'citizenship'  => null,
            'mobile_no'    => '09171234567',
        ]);

        return User::create([
            'resident_id'       => $resident->id,
            'email'             => 'resident@example.com',
            'password'          => bcrypt('ChosenPass1!'),
            'is_active'         => true,
            'email_verified_at' => now(),
            'profile_status'    => ProfileStatus::INCOMPLETE,
        ]);
    }

    private function submitted(): User
    {
        Storage::fake('local');

        $user = $this->registered();
        $this->primaryAddress();

        app(SubmitProfileVerification::class)->handle($user, [
            'form' => [
                'philsys_card_no'        => '',
                'surname'                => 'Dela Cruz',
                'first_name'             => 'Juan',
                'middle_name'            => 'Santos',
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
            'property'  => ['block' => '7', 'lot' => '12'],
            'household' => ['type' => 'owner', 'prev_address' => ''],
        ], UploadedFile::fake()->image('id.jpg'));

        return $user->fresh();
    }

    private function primaryAddress(): Address
    {
        return Address::create([
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
    }

    private function officer(): Admin
    {
        static $sequence = 0;

        $sequence++;

        $role = Role::create([
            'name'        => 'Verifier ' . $sequence,
            'permissions' => ['homeowners.edit', 'residents.view'],
        ]);

        return Admin::create([
            'name'      => 'Officer ' . $sequence,
            'email'     => 'verifier' . $sequence . '@example.com',
            'password'  => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }
}
