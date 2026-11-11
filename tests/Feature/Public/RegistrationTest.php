<?php

namespace Tests\Feature\Public;

use App\Actions\Homeowners\Users\AcceptRegistration;
use App\Enums\RegistrationStatus;
use App\Http\Livewire\Auth\RegisterWizard;
use App\Mail\RegistrationReceived;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\HomeownerRegisteredNotification;
use App\Notifications\RegistrationApprovedNotification;
use App\Queries\Auth\RegistrationFormQuery;
use App\Utility\SettingsHelper;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The five-step registration: the applicant chooses their own password, nothing
 * is written before the last step, and no password ever travels by email.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // ===== Screen =====

    public function test_the_registration_screen_is_public(): void
    {
        $this->get('/portal/register')
            ->assertOk()
            ->assertSee('Account')
            ->assertSee('Password')
            ->assertSee('js/contact-input.js');
    }

    public function test_a_signed_in_account_is_sent_to_its_panel_from_the_registration_screen(): void
    {
        $this->actingAs(factory(User::class)->create(), 'homeowner')
            ->get('/portal/register')
            ->assertRedirect('/portal');
    }

    // ===== Submission =====

    public function test_a_registration_creates_a_pending_account_a_resident_and_the_household(): void
    {
        Mail::fake();
        Storage::fake('local');

        $property = $this->property(7, 12, 'TH 2');

        $this->wizard($property)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('registered', true)
            ->assertSet('pendingApproval', true);

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        $this->assertTrue($user->isPendingRegistration(), 'account is pending');
        $this->assertSame(RegistrationStatus::PENDING, $user->registration_status->value);
        $this->assertTrue(Hash::check('ChosenPass1!', $user->password), 'password hashed from the chosen one');
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertNotSame('ChosenPass1!', $user->password);

        $resident = $user->resident;
        $this->assertInstanceOf(Resident::class, $resident);
        $this->assertSame('Dela Cruz', $resident->surname);
        $this->assertNotNull($resident->valid_id_path);
        Storage::disk('local')->assertExists($resident->valid_id_path);

        $household = Household::where('property_id', $property->id)->firstOrFail();
        $this->assertSame($resident->id, (int) $household->head_resident?->id);
        $this->assertTrue(
            HouseholdMember::where('household_id', $household->id)
                ->where('resident_id', $resident->id)
                ->where('is_head', true)
                ->exists(),
            'resident is the household head'
        );

        $this->assertGuest('homeowner');
    }

    public function test_the_receipt_email_carries_no_password(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->wizard($this->property(7, 13, 'TH 2'))->call('submit')->assertHasNoErrors();

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        Mail::assertSent(RegistrationReceived::class, function (RegistrationReceived $mail) use ($user) {
            return $mail->hasTo($user->email)
                && ! property_exists($mail, 'temporaryPassword');
        });

        $template = file_get_contents(resource_path('views/emails/registration-received.blade.php'));

        $this->assertStringNotContainsString('password', strtolower($template));
    }

    public function test_an_occupied_unit_requires_the_applicant_to_confirm_the_link(): void
    {
        Mail::fake();
        Storage::fake('local');

        $head = $this->existingHousehold();

        $this->wizard($head->household->property)
            ->call('submit')
            ->assertHasErrors(['link_household']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    public function test_a_registration_at_an_occupied_unit_links_without_replacing_the_head(): void
    {
        Mail::fake();
        Storage::fake('local');

        $head = $this->existingHousehold();
        $property = $head->household->property;

        $this->wizard($property)
            ->set('link_household', true)
            ->call('submit')
            ->assertHasNoErrors();

        $household = $property->fresh()->currentHousehold;

        $this->assertSame($head->resident_id, (int) $household->head_resident?->id);

        $member = User::where('email', 'applicant@example.com')->firstOrFail()->resident;

        $this->assertTrue(
            HouseholdMember::where('household_id', $household->id)
                ->where('resident_id', $member->id)
                ->where('is_head', false)
                ->exists()
        );
    }

    public function test_an_email_address_an_account_already_holds_is_refused(): void
    {
        Mail::fake();
        Storage::fake('local');

        factory(User::class)->create(['email' => 'applicant@example.com']);

        $this->wizard($this->property(3, 9, 'TH 1'))
            ->call('submit')
            ->assertHasErrors(['email']);

        $this->assertSame(1, User::where('email', 'applicant@example.com')->count());
    }

    public function test_the_review_step_shows_the_account_email_and_never_the_password(): void
    {
        $this->wizard($this->property(2, 2, 'TH 1'))
            ->call('next')
            ->call('next')
            ->call('next')
            ->call('next')
            ->assertSet('step', 5)
            ->assertSee('applicant@example.com')
            ->assertDontSee('ChosenPass1!');
    }

    public function test_the_mobile_number_field_asks_for_an_eleven_digit_number(): void
    {
        $this->wizard($this->property(2, 9, 'TH 1'))
            ->call('next')
            ->assertSet('step', 2)
            ->assertSeeHtml('data-thha-contact')
            ->assertSeeHtml('maxlength="11"');
    }

    public function test_the_registration_collects_the_sectoral_tags(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->wizard($this->property(6, 4, 'TH 1'))
            ->set('form.sectors', ['pwd', 'senior_citizen'])
            ->call('submit')
            ->assertHasNoErrors();

        $resident = User::where('email', 'applicant@example.com')->firstOrFail()->resident;

        $this->assertSame(['pwd', 'senior_citizen'], $resident->sectorValues());
    }

    public function test_the_spouse_name_is_asked_for_only_when_married(): void
    {
        $this->wizard($this->property(6, 5, 'TH 1'))
            ->set('form.civil_status', 'single')
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 2)
            ->assertSee('Sectoral Tags')
            ->assertDontSee('Spouse Name')
            ->set('form.civil_status', 'married')
            ->assertSee('Spouse Name');
    }

    // ===== Consent and password =====

    public function test_the_terms_and_the_privacy_consent_are_separate_boxes(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->wizard($this->property(3, 10, 'TH 1'))
            ->set('consent', ['terms' => true, 'privacy' => false])
            ->call('submit')
            ->assertHasErrors(['consent.privacy']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);

        $this->wizard($this->property(3, 14, 'TH 1'))
            ->set('consent', ['terms' => false, 'privacy' => true])
            ->call('submit')
            ->assertHasErrors(['consent.terms']);
    }

    public function test_the_review_step_shows_a_checkbox_and_a_modal_for_each_consent(): void
    {
        $this->wizard($this->property(2, 3, 'TH 1'))
            ->call('next')
            ->call('next')
            ->call('next')
            ->call('next')
            ->assertSet('step', 5)
            ->assertSeeHtml('id="registrationTermsModal"')
            ->assertSeeHtml('id="registrationPrivacyModal"');
    }

    public function test_the_password_must_follow_the_office_policy(): void
    {
        $this->setSecurity(['password_min_length' => '12']);

        $this->wizard($this->property(3, 11, 'TH 1'))
            ->set('password', 'short1!A')
            ->set('password_confirmation', 'short1!A')
            ->call('submit')
            ->assertHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    public function test_an_auto_accepted_registration_can_sign_in_with_the_chosen_password(): void
    {
        Mail::fake();
        Storage::fake('local');

        Setting::create(['key' => 'auto_accept_registration', 'value' => '1', 'group' => 'auth']);
        SettingsHelper::flush('auth');

        $this->wizard($this->property(3, 12, 'TH 1'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('pendingApproval', false);

        $this->post('/login', [
            'email'    => 'applicant@example.com',
            'password' => 'ChosenPass1!',
        ])->assertRedirect('/portal');

        $this->assertAuthenticated('homeowner');
    }

    // ===== Address =====

    public function test_naming_a_unit_already_on_record_looks_it_up_at_once(): void
    {
        $this->wizard($this->property(6, 6, 'TH 1'))
            ->set('property.block', '')
            ->set('property.lot', '')
            ->assertSet('address', null)
            ->set('property.block', '6')
            ->set('property.lot', '6')
            ->assertSet('address.address', 'Blk 6 Lot 6 · Tradition Homes · Mabini Street · Trece Martires City');
    }

    public function test_a_unit_on_record_shows_its_address_and_its_household_state(): void
    {
        $this->wizard($this->property(5, 5, 'TH 1'))
            ->call('next')
            ->call('next')
            ->assertSet('step', 3)
            ->call('next')
            ->assertSet('step', 4)
            ->assertSet('address.is_occupied', false)
            ->assertSet('address.address', 'Blk 5 Lot 5 · Tradition Homes · Mabini Street · Trece Martires City');
    }

    public function test_a_block_and_lot_names_one_unit(): void
    {
        $address = Address::create([
            'label'       => 'Shared address',
            'street'      => 'Mabini Street',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
        ]);

        $unit = Property::create(['block' => 5, 'lot' => 5, 'address_id' => $address->id]);

        $this->assertSame($unit->id, app(RegistrationFormQuery::class)->propertyAt(5, 5)->id);
        $this->assertNull(app(RegistrationFormQuery::class)->propertyAt(5, 6));
    }

    public function test_a_unit_with_no_record_has_to_be_described_before_the_wizard_moves_on(): void
    {
        Address::create([
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

        $this->wizard(null, ['block' => '11', 'lot' => '4'])
            ->call('next')
            ->call('next')
            ->set('property.type', '')
            ->call('next')
            ->assertSet('step', 3)
            ->assertHasErrors(['property.type']);
    }

    public function test_an_unlisted_unit_is_created_together_with_the_registration(): void
    {
        Mail::fake();
        Storage::fake('local');

        $address = $this->primaryAddress();

        $this->wizard(null, [
            'block' => '11',
            'lot'   => '4',
            'type'  => 'house',
        ])->call('submit')->assertHasNoErrors();

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        $property = Property::where('block', 11)->where('lot', 4)->firstOrFail();

        $this->assertSame($property->id, (int) $user->household->property_id);
        $this->assertSame($address->id, (int) $property->address_id);
        $this->assertSame($property->id, (int) $property->currentHousehold->property_id);
    }

    public function test_the_review_step_names_an_unlisted_unit_as_a_new_one(): void
    {
        $this->primaryAddress();

        $this->wizard(null, [
            'block' => '11',
            'lot'   => '5',
            'type'  => 'house',
        ])->call('next')
            ->call('next')
            ->assertSet('step', 3)
            ->call('next')
            ->call('next')
            ->assertSet('step', 5)
            ->assertSee('Blk 11 Lot 5');
    }

    // ===== Abuse =====

    public function test_a_connection_cannot_file_registrations_without_limit(): void
    {
        Mail::fake();
        Storage::fake('local');
        Notification::fake();

        $key = 'registration:' . request()->ip();

        $limiter = app(RateLimiter::class);

        for ($attempt = 0; $attempt < RegisterWizard::REGISTRATION_LIMIT; $attempt++) {
            $limiter->hit($key, RegisterWizard::REGISTRATION_DECAY);
        }

        $this->wizard($this->property(9, 9, 'TH 1'))
            ->call('submit')
            ->assertHasErrors('registration');

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    // ===== Notifications =====

    public function test_a_registration_tells_the_officers_who_may_see_homeowners(): void
    {
        Notification::fake();
        Mail::fake();
        Storage::fake('local');

        $officer = $this->officer(['homeowners.view']);
        $other = $this->officer([]);

        $this->wizard($this->property(6, 2, 'TH 1'))->call('submit')->assertHasNoErrors();

        Notification::assertSentTo($officer, HomeownerRegisteredNotification::class);
        Notification::assertNotSentTo($other, HomeownerRegisteredNotification::class);
    }

    public function test_accepting_a_pending_registration_opens_the_account(): void
    {
        Notification::fake();

        $applicant = factory(User::class)->create([
            'registration_status' => RegistrationStatus::PENDING,
            'is_active'           => true,
        ]);

        app(AcceptRegistration::class)->handle($applicant->id, $this->officer(['homeowners.edit']));

        $applicant->refresh();

        $this->assertSame(RegistrationStatus::APPROVED, $applicant->registration_status->value);
        $this->assertFalse($applicant->isPendingRegistration());
        $this->assertTrue((bool) $applicant->is_active);
        Notification::assertSentTo($applicant, RegistrationApprovedNotification::class);
    }

    // ===== Tools =====

    private function setSecurity(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value, 'group' => 'security']);
        }

        SettingsHelper::flush('security');
    }

    private function officer(array $permissions): Admin
    {
        static $sequence = 0;

        $sequence++;

        $role = Role::create([
            'name'        => 'Test role ' . $sequence,
            'permissions' => $permissions,
        ]);

        return Admin::create([
            'name'      => 'Officer ' . $sequence,
            'email'     => 'officer' . $sequence . '@example.com',
            'password'  => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }

    private function wizard(?Property $property, array $newUnit = []): \Livewire\Testing\TestableLivewire
    {
        $address = $property !== null
            ? [
                'block'       => (string) $property->block,
                'lot'         => (string) $property->lot,
                'street'      => (string) $property->address->street,
                'subdivision' => (string) $property->address->subdivision,
                'city'        => (string) $property->address->city,
            ]
            : array_merge(
                ['block' => '', 'lot' => '', 'street' => '', 'subdivision' => '', 'city' => ''],
                $newUnit
            );

        return Livewire::test(RegisterWizard::class)
            ->set('email', 'applicant@example.com')
            ->set('password', 'ChosenPass1!')
            ->set('password_confirmation', 'ChosenPass1!')
            ->set('form', [
                'surname'                => 'Dela Cruz',
                'first_name'             => 'Juan',
                'middle_name'            => 'Santos',
                'suffix'                 => '',
                'birth_date'             => '1990-05-12',
                'birth_place'            => 'Trece Martires City',
                'mobile_no'              => '09171234567',
                'sex'                    => 'male',
                'civil_status'           => 'married',
                'spouse_name'            => 'Maria Dela Cruz',
                'religion'               => 'Roman Catholic',
                'citizenship'            => 'Filipino',
                'occupation'             => 'Teacher',
                'educational_attainment' => 'college_graduate',
            ])
            ->set('property', $address)
            ->set('idFile', UploadedFile::fake()->image('valid-id.jpg'))
            ->set('consent', ['terms' => true, 'privacy' => true]);
    }

    private function property(int $block, int $lot, string $phase): Property
    {
        $address = Address::create([
            'label'       => 'Unit ' . $phase . ' ' . $block . '-' . $lot,
            'street'      => 'Mabini Street',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
        ]);

        return Property::create([
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $address->id,
        ]);
    }

    /**
     * The association's own address, which a unit the office has no record of is
     * filed under.
     */
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

    private function existingHousehold(): User
    {
        $property = $this->property(4, 12, 'TH 2');

        $resident = factory(Resident::class)->create(['surname' => 'Santos']);
        $head = factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $property->id,
            'type'             => 'owner',
        ]);

        $household->placeHead($resident->id, 'father');

        return $head;
    }
}
