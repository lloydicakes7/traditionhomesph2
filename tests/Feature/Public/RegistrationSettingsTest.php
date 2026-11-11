<?php

namespace Tests\Feature\Public;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Setting;
use App\Utility\SettingsHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The three registration switches: each one is enforced on the route, not by
 * hiding a link. A closed screen answers as not found (or with a notice), and a
 * registration that is not auto-accepted cannot sign in until approved.
 */
class RegistrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Resident registration =====

    public function test_resident_registration_is_open_by_default(): void
    {
        $this->get('/portal/register')->assertOk();

        $this->setAuth(['allow_resident_registration' => '1']);

        $this->assertSame('portal/register', config('adminlte.register_url'));
    }

    public function test_closing_resident_registration_closes_the_route_and_the_link(): void
    {
        $this->setAuth(['allow_resident_registration' => '0']);

        $this->get('/portal/register')
            ->assertStatus(403)
            ->assertSee(trans('auth.register_closed_title'));

        $this->get('/')->assertDontSee('href="' . route('register') . '"', false);
        $this->get('/login')->assertDontSee('href="' . route('register') . '"', false);
    }

    public function test_auto_accept_off_files_a_pending_account_that_cannot_sign_in(): void
    {
        $this->setAuth(['auto_accept_registration' => '0']);

        $user = \App\Models\User::create([
            'email'     => 'pending@example.com',
            'password'  => Hash::make('ChosenPass1!'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email'    => 'pending@example.com',
            'password' => 'ChosenPass1!',
        ])->assertRedirect(route('registration.pending'));

        $this->assertGuest('homeowner');
    }

    // ===== Officer registration =====

    public function test_officer_registration_is_closed_by_default(): void
    {
        $this->get('/admin/register')->assertStatus(403);
        $this->post('/admin/register', [])->assertNotFound();
    }

    public function test_officer_registration_opens_only_when_allowed_and_files_an_inactive_account(): void
    {
        $this->seedRole();

        $this->setAuth(['allow_admin_registration' => '1']);

        $this->get('/admin/register')->assertOk();

        $this->post('/admin/register', [
            'name'                  => 'New Officer',
            'email'                 => 'officer.new@example.com',
            'password'              => 'ChosenPass1!',
            'password_confirmation' => 'ChosenPass1!',
        ])->assertRedirect(route('registration.pending'));

        $admin = Admin::where('email', 'officer.new@example.com')->firstOrFail();

        $this->assertFalse((bool) $admin->is_active);
        $this->assertTrue(Hash::check('ChosenPass1!', $admin->password));
        $this->assertSame('Visitor', $admin->role->name);
    }

    public function test_an_unapproved_officer_cannot_sign_in(): void
    {
        $this->seedRole();

        Admin::create([
            'name'      => 'New Officer',
            'email'     => 'officer.pending@example.com',
            'password'  => Hash::make('ChosenPass1!'),
            'role_id'   => Role::where('name', 'Visitor')->value('id'),
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email'    => 'officer.pending@example.com',
            'password' => 'ChosenPass1!',
        ])->assertRedirect(route('registration.pending'));

        $this->assertGuest('web');
    }

    // ===== Two-factor is gone =====

    public function test_no_two_factor_route_exists(): void
    {
        $this->get('/admin/two-factor')->assertNotFound();
        $this->post('/admin/two-factor', ['code' => '123456'])->assertNotFound();
        $this->post('/admin/two-factor/resend')->assertNotFound();
    }

    // ===== Tools =====

    private function setAuth(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value, 'group' => 'auth']);
        }

        SettingsHelper::flush('auth');

        (new \App\Providers\FrameworkServiceProvider($this->app))->boot();
    }

    private function seedRole(): void
    {
        Role::create([
            'name'        => 'Visitor',
            'description' => 'Visitor',
            'permissions' => [],
        ]);
    }
}
