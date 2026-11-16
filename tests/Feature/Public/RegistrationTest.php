<?php

namespace Tests\Feature\Public;

use App\Http\Livewire\Auth\RegisterWizard;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\HomeownerRegisteredNotification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The three-step registration: the applicant gives only who they are and the
 * address they sign in with, and the account opens when the email is verified.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    // ===== Screen =====

    public function test_the_registration_screen_opens_on_the_personal_step(): void
    {
        $this->get('/portal/register')
            ->assertOk()
            ->assertSee('Personal information')
            ->assertSee('Mobile No.')
            ->assertSee('js/contact-input.js');
    }

    public function test_a_signed_in_account_is_sent_to_its_panel_from_the_registration_screen(): void
    {
        $this->actingAs(factory(User::class)->create(), 'homeowner')
            ->get('/portal/register')
            ->assertRedirect('/portal');
    }

    public function test_the_wizard_asks_only_the_seven_personal_fields(): void
    {
        $this->wizard()
            ->assertSet('step', 1)
            ->assertSeeHtml('name="form.surname"')
            ->assertSeeHtml('name="form.first_name"')
            ->assertSeeHtml('name="form.middle_name"')
            ->assertSeeHtml('name="form.suffix"')
            ->assertSeeHtml('name="form.birth_date"')
            ->assertSeeHtml('name="form.mobile_no"')
            ->assertSeeHtml('name="form.sex"')
            ->assertSeeHtml('name="form.civil_status"')
            ->assertDontSeeHtml('name="form.spouse_name"')
            ->assertDontSeeHtml('name="form.birth_place"')
            ->assertDontSeeHtml('name="form.religion"')
            ->assertDontSeeHtml('name="form.citizenship"')
            ->assertDontSeeHtml('name="form.philsys_card_no"')
            ->assertDontSeeHtml('name="form.sectors"')
            ->assertDontSeeHtml('wire:model="idFile"');
    }

    public function test_the_three_steps_run_personal_then_account_then_review(): void
    {
        $this->wizard()
            ->call('next')
            ->assertSet('step', 2)
            ->assertSee('Password')
            ->call('next')
            ->assertSet('step', 3)
            ->assertSee('applicant@example.com')
            ->assertSeeHtml('id="registrationTermsModal"')
            ->assertDontSee('ChosenPass1!');
    }

    // ===== Submission =====

    public function test_a_registration_writes_the_person_and_the_account_only(): void
    {
        Notification::fake();

        $this->wizard()
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('registered', true);

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        $this->assertInstanceOf(Resident::class, $user->resident);
        $this->assertSame('Dela Cruz', $user->resident->surname);
        $this->assertSame('09171234567', $user->resident->mobile_no);
        $this->assertTrue(Hash::check('ChosenPass1!', $user->password), 'password hashed from the chosen one');
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertNull($user->resident->valid_id_path);
        $this->assertSame(0, Household::count());
        $this->assertSame(0, Property::count());

        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertGuest('homeowner');
    }

    public function test_an_email_address_an_account_already_holds_is_refused(): void
    {
        Notification::fake();

        factory(User::class)->create(['email' => 'applicant@example.com']);

        $this->wizard()
            ->call('submit')
            ->assertHasErrors(['email']);

        $this->assertSame(1, User::where('email', 'applicant@example.com')->count());
    }

    // ===== Email verification =====

    public function test_the_account_cannot_sign_in_until_the_email_is_verified(): void
    {
        Notification::fake();

        $this->wizard()->call('submit')->assertHasNoErrors();

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        $this->post('/login', [
            'email'    => 'applicant@example.com',
            'password' => 'ChosenPass1!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('homeowner');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id'   => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect(route('login'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $this->post('/login', [
            'email'    => 'applicant@example.com',
            'password' => 'ChosenPass1!',
        ])->assertRedirect('/portal');

        $this->assertAuthenticated('homeowner');
    }

    public function test_a_verification_link_for_another_address_is_refused(): void
    {
        Notification::fake();

        $this->wizard()->call('submit');

        $user = User::where('email', 'applicant@example.com')->firstOrFail();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id'   => $user->id,
            'hash' => sha1('someone-else@example.com'),
        ]);

        $this->get($url)->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    // ===== Personal fields =====

    public function test_a_married_applicant_files_no_spouse_from_the_register_page(): void
    {
        Notification::fake();

        $this->wizard(['civil_status' => 'married'])
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('registered', true);

        $this->assertNull(User::where('email', 'applicant@example.com')->firstOrFail()->resident->spouse_name);
    }

    public function test_the_suffix_and_the_middle_name_may_be_left_blank(): void
    {
        $this->wizard(['middle_name' => '', 'suffix' => ''])
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 2);
    }

    public function test_the_mobile_number_must_be_eleven_digits(): void
    {
        $this->wizard(['mobile_no' => '12345'])
            ->call('next')
            ->assertHasErrors(['form.mobile_no']);
    }

    // ===== Password =====

    public function test_the_password_note_lists_the_four_criteria(): void
    {
        $this->wizard()
            ->call('next')
            ->assertSet('step', 2)
            ->assertSee('<li>At least 8 characters</li>', false)
            ->assertSee('<li>One uppercase letter</li>', false)
            ->assertSee('<li>One lowercase letter</li>', false)
            ->assertSee('<li>One number</li>', false)
            ->assertSee('<li>One special character</li>', false);
    }

    public function test_a_password_shorter_than_eight_characters_is_refused(): void
    {
        $this->wizard([], ['password' => 'Short1!', 'password_confirmation' => 'Short1!'])
            ->call('submit')
            ->assertHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    public function test_a_password_without_every_criterion_is_refused(): void
    {
        $this->wizard([], ['password' => 'Chosenpass1', 'password_confirmation' => 'Chosenpass1'])
            ->call('submit')
            ->assertHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    public function test_the_password_must_follow_the_office_policy(): void
    {
        $this->setSecurity(['password_min_length' => '12']);

        $this->wizard([], ['password' => 'short1!A', 'password_confirmation' => 'short1!A'])
            ->call('submit')
            ->assertHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    // ===== Consent =====

    public function test_the_terms_and_the_privacy_consent_are_separate_boxes(): void
    {
        Notification::fake();

        $this->wizard([], [], ['terms' => true, 'privacy' => false])
            ->call('submit')
            ->assertHasErrors(['consent.privacy']);

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);

        $this->wizard([], ['email' => 'applicant2@example.com'], ['terms' => false, 'privacy' => true])
            ->call('submit')
            ->assertHasErrors(['consent.terms']);
    }

    // ===== Abuse =====

    public function test_a_connection_cannot_file_registrations_without_limit(): void
    {
        Notification::fake();

        $key = 'registration:' . request()->ip();

        $limiter = app(RateLimiter::class);

        for ($attempt = 0; $attempt < RegisterWizard::REGISTRATION_LIMIT; $attempt++) {
            $limiter->hit($key, RegisterWizard::REGISTRATION_DECAY);
        }

        $this->wizard()
            ->call('submit')
            ->assertHasErrors('registration');

        $this->assertDatabaseMissing('users', ['email' => 'applicant@example.com']);
    }

    // ===== Notifications =====

    public function test_a_registration_tells_the_officers_who_may_see_homeowners(): void
    {
        Notification::fake();

        $officer = $this->officer(['homeowners.view']);
        $other = $this->officer([]);

        $this->wizard()->call('submit')->assertHasNoErrors();

        Notification::assertSentTo($officer, HomeownerRegisteredNotification::class);
        Notification::assertNotSentTo($other, HomeownerRegisteredNotification::class);
    }

    // ===== Tools =====

    private function setSecurity(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value, 'group' => 'security']);
        }

        \App\Utility\SettingsHelper::flush('security');
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

    /**
     * @param  array<string, mixed>  $formOverrides
     * @param  array<string, mixed>  $accountOverrides
     * @param  array<string, bool>  $consent
     */
    private function wizard(array $formOverrides = [], array $accountOverrides = [], array $consent = ['terms' => true, 'privacy' => true]): \Livewire\Testing\TestableLivewire
    {
        return Livewire::test(RegisterWizard::class)
            ->set('email', $accountOverrides['email'] ?? 'applicant@example.com')
            ->set('password', $accountOverrides['password'] ?? 'ChosenPass1!')
            ->set('password_confirmation', $accountOverrides['password_confirmation'] ?? 'ChosenPass1!')
            ->set('form', array_merge([
                'surname'      => 'Dela Cruz',
                'first_name'   => 'Juan',
                'middle_name'  => 'Santos',
                'suffix'       => '',
                'mobile_no'    => '09171234567',
                'birth_date'   => '1990-05-12',
                'sex'          => 'male',
                'civil_status' => 'single',
            ], $formOverrides))
            ->set('consent', $consent);
    }
}
