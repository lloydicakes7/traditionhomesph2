<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Settings\AuthenticationSettings;
use App\Http\Livewire\Portal\Profile\PasswordChange;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Queries\Settings\AuthenticationSettingsQuery;
use App\Utility\SettingsHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SecuritySettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Drop any frozen clock so a time-travelling test cannot leak into the next.
     */
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Write the security settings this test needs and drop the cached group.
     */
    private function setSecurity(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'security'],
                ['key' => $key, 'value' => $value, 'group' => 'security']
            );
        }

        SettingsHelper::flush('security');
    }

    private function makeRole(string $name): Role
    {
        return Role::firstOrCreate(
            ['name' => $name],
            ['description' => $name, 'permissions' => []]
        );
    }

    private function makeAdmin(array $overrides = []): Admin
    {
        return Admin::create(array_merge([
            'name'                => 'Officer',
            'email'               => 'officer' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ], $overrides));
    }

    private function seedRoles(): void
    {
        foreach (\App\Enums\DefaultRoleSlug::labels() as $name => $label) {
            $this->makeRole($name);
        }
    }

    // ---------------------------------------------------------------
    // 1. Password policy
    // ---------------------------------------------------------------

    public function test_changing_the_password_rejects_a_value_that_breaks_the_policy()
    {
        $this->setSecurity([
            'password_min_length'     => '10',
            'password_require_upper'  => '1',
            'password_require_number' => '1',
            'password_require_symbol' => '1',
        ]);

        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner');

        Livewire::test(PasswordChange::class)
            ->set('current_password', 'password')
            ->set('new_password', 'weakpassword')
            ->set('new_password_confirmation', 'weakpassword')
            ->call('save')
            ->assertHasErrors('new_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_changing_the_password_stamps_the_new_value()
    {
        $this->setSecurity(['password_min_length' => '4']);

        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner');

        Livewire::test(PasswordChange::class)
            ->set('current_password', 'password')
            ->set('new_password', 'NewSecret1!')
            ->set('new_password_confirmation', 'NewSecret1!')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewSecret1!', $user->fresh()->password));
    }

    // ---------------------------------------------------------------
    // 2. Login attempts
    // ---------------------------------------------------------------

    public function test_login_is_locked_out_after_the_configured_number_of_attempts()
    {
        $this->setSecurity(['login_max_attempts' => '2', 'login_lockout_minutes' => '5']);

        $this->makeAdmin(['email' => 'locked@example.test']);

        for ($i = 0; $i < 2; $i++) {
            $this->post('/login', ['email' => 'locked@example.test', 'password' => 'wrong']);
        }

        $response = $this->post('/login', ['email' => 'locked@example.test', 'password' => 'Secret123!']);

        $this->assertGuest('web');
        $response->assertSessionHasErrors('email');
    }

    // ---------------------------------------------------------------
    // 3. Session timeout
    // ---------------------------------------------------------------

    public function test_session_timeout_setting_reaches_framework_config()
    {
        $this->setSecurity(['session_timeout' => '45', 'session_expire_on_close' => '1']);

        (new \App\Providers\FrameworkServiceProvider($this->app))->boot();

        $this->assertSame(45, (int) config('session.lifetime'));
        $this->assertTrue((bool) config('session.expire_on_close'));
    }

    // ---------------------------------------------------------------
    // 4. Password expiry
    // ---------------------------------------------------------------

    public function test_expired_password_parks_the_account_on_the_profile_page()
    {
        $this->seedRoles();
        $this->setSecurity(['password_expiry_days' => '30']);

        $admin = $this->makeAdmin([
            'role_id'             => $this->makeRole('Administrator')->id,
            'password_changed_at' => now()->subDays(31),
        ]);

        $this->actingAs($admin, 'web');

        $this->get('/admin')->assertRedirect(route('settings.profile'));
        $this->get('/admin/settings/profile')->assertOk();

        $this->post('/admin/logout');
        $this->assertGuest('web');
    }

    public function test_password_inside_the_window_is_not_parked()
    {
        $this->seedRoles();
        $this->setSecurity(['password_expiry_days' => '30']);

        $admin = $this->makeAdmin([
            'role_id'             => $this->makeRole('Administrator')->id,
            'password_changed_at' => now()->subDays(5),
        ]);

        $this->actingAs($admin, 'web');

        $this->get('/admin')->assertOk();
    }

    // ---------------------------------------------------------------
    // 5. Authentication settings screen
    // ---------------------------------------------------------------

    public function test_the_authentication_page_saves_the_three_switches()
    {
        $this->seedRoles();
        $admin = $this->makeAdmin(['role_id' => $this->makeRole('Administrator')->id]);
        $this->actingAs($admin, 'web');

        Livewire::test(AuthenticationSettings::class)
            ->set('allow_admin_registration', true)
            ->set('allow_resident_registration', false)
            ->set('auto_accept_registration', true)
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(AuthenticationSettingsQuery::class);

        $this->assertTrue($settings->adminRegistrationAllowed());
        $this->assertFalse($settings->residentRegistrationAllowed());
        $this->assertTrue($settings->autoAccept());
    }

    public function test_the_authentication_page_is_closed_to_a_role_other_than_the_administrator()
    {
        $this->seedRoles();
        $admin = $this->makeAdmin(['role_id' => $this->makeRole('Visitor')->id]);
        $this->actingAs($admin, 'web');

        $this->get('/admin/settings/authentication')->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 6. The office's own account form
    // ---------------------------------------------------------------

    public function test_the_office_account_form_holds_the_configured_password_policy()
    {
        $this->seedRoles();
        $admin = $this->makeAdmin(['role_id' => $this->makeRole('Administrator')->id]);
        $this->actingAs($admin, 'web');

        $this->setSecurity([
            'password_min_length'     => '12',
            'password_require_upper'  => '1',
            'password_require_symbol' => '1',
        ]);

        $role = $this->makeRole('Staff');

        Livewire::test(\App\Http\Livewire\Admin\UserManagement\User\Modals\UserFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'New Staff')
            ->set('form.email', 'staff.new@example.test')
            ->set('form.role_id', (string) $role->id)
            ->set('form.password', 'shortpass')
            ->set('form.password_confirmation', 'shortpass')
            ->call('save')
            ->assertHasErrors('form.password');

        $this->assertDatabaseMissing('admins', ['email' => 'staff.new@example.test']);
    }
}
