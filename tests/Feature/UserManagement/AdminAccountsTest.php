<?php

namespace Tests\Feature\UserManagement;

use App\Actions\UserManagement\Users\CreateAdmin;
use App\Actions\UserManagement\Users\ResetAdminPassword;
use App\Actions\UserManagement\Users\UpdateAdmin;
use App\Http\Livewire\Admin\UserManagement\User\Modals\AdminResetPasswordModal;
use App\Http\Livewire\Admin\UserManagement\User\Modals\UserFormModal;
use App\Models\Admin;
use App\Models\Role;
use App\Notifications\OfficerAccountOpened;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The office never sets or reads an officer's password: an account opens with
 * one nobody knows and a mailed link, and a reset does the same again.
 */
class AdminAccountsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Create =====

    public function test_a_new_account_gets_an_unusable_password_and_a_welcome(): void
    {
        Notification::fake();

        $role = $this->role('Administrator');
        $by = $this->admin($role);

        $admin = app(CreateAdmin::class)->handle([
            'name'      => 'New Officer',
            'email'     => 'new.officer@example.test',
            'role_id'   => $role->id,
            'is_active' => true,
        ], $by);

        $this->assertSame('new.officer@example.test', $admin->email);
        $this->assertFalse(Hash::check('', $admin->password));
        $this->assertGreaterThan(20, strlen($admin->password));
        $this->assertNotNull($admin->password_changed_at);

        Notification::assertSentTo($admin, OfficerAccountOpened::class, function (OfficerAccountOpened $notification) use ($admin) {
            return $notification->toMail($admin)->subject === __('auth.officer_welcome_subject');
        });

        Notification::assertNotSentTo($admin, ResetPasswordNotification::class);

        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $by->id,
            'action'   => 'created',
            'module'   => 'user',
        ]);
    }

    public function test_the_welcome_link_lets_the_officer_choose_the_password_and_signs_them_in(): void
    {
        Notification::fake();

        $role = $this->role('Administrator');
        $by = $this->admin($role, 'actor@example.test');

        $officer = app(CreateAdmin::class)->handle([
            'name'      => 'New Officer',
            'email'     => 'new.officer@example.test',
            'role_id'   => $role->id,
            'is_active' => true,
        ], $by);

        $url = $this->linkFor($officer);

        $this->get($url)->assertOk();

        $this->post('/password/reset', [
            'token'                 => basename((string) parse_url($url, PHP_URL_PATH)),
            'email'                 => $officer->email,
            'password'              => 'ChosenPass1!',
            'password_confirmation' => 'ChosenPass1!',
        ]);

        $this->assertTrue(Hash::check('ChosenPass1!', $officer->fresh()->password));
        $this->assertAuthenticatedAs($officer->fresh(), 'web');
    }

    // ===== Update =====

    public function test_an_edit_never_touches_the_password(): void
    {
        $role = $this->role('Administrator');
        $by = $this->admin($role, 'actor@example.test');
        $officer = $this->admin($role, 'officer@example.test');

        $officer->forceFill(['password' => Hash::make('OldSecret1!')])->save();

        app(UpdateAdmin::class)->handle($officer, [
            'name'      => 'Renamed Officer',
            'email'     => $officer->email,
            'role_id'   => $role->id,
            'is_active' => true,
            'password'  => 'TypedByAnOfficer1!',
        ], $by);

        $this->assertSame('Renamed Officer', $officer->fresh()->name);
        $this->assertTrue(Hash::check('OldSecret1!', $officer->fresh()->password));
    }

    // ===== Reset =====

    public function test_resetting_an_officers_password_invalidates_the_old_one_and_mails_a_link(): void
    {
        Notification::fake();

        $role = $this->role('Administrator');
        $by = $this->admin($role, 'actor@example.test');
        $officer = $this->admin($role, 'officer@example.test');

        $officer->forceFill(['password' => Hash::make('OldSecret1!')])->save();

        app(ResetAdminPassword::class)->handle($officer->id, $by);

        $this->assertFalse(Hash::check('OldSecret1!', $officer->fresh()->password));
        Notification::assertSentTo($officer, ResetPasswordNotification::class);

        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $by->id,
            'action'   => 'reset_password',
            'module'   => 'user',
        ]);
    }

    // ===== Policy =====

    public function test_an_administrator_may_reset_another_officers_password(): void
    {
        $role = $this->role('Administrator');
        $by = $this->admin($role);
        $other = $this->admin($role, 'other@example.test');

        $this->assertTrue($by->can('resetPassword', $other));
    }

    public function test_nobody_resets_their_own_password_from_the_list(): void
    {
        $role = $this->role('Administrator');
        $by = $this->admin($role);

        $this->assertFalse($by->can('resetPassword', $by));
    }

    public function test_an_officer_who_is_not_an_administrator_may_not_reset_a_password(): void
    {
        $by = $this->admin($this->role('Staff'));
        $other = $this->admin($this->role('Administrator'), 'other@example.test');

        $this->assertFalse($by->can('resetPassword', $other));
    }

    // ===== Screen =====

    public function test_the_account_form_asks_for_no_password(): void
    {
        $by = $this->admin($this->role('Administrator'));

        Livewire::actingAs($by, 'web')
            ->test(UserFormModal::class)
            ->call('openCreate')
            ->assertSet('form.password', null)
            ->assertDontSee('form.password')
            ->assertDontSee('Confirm Password');
    }

    public function test_the_reset_dialog_resets_the_account_it_was_opened_for(): void
    {
        Notification::fake();

        $role = $this->role('Administrator');
        $by = $this->admin($role, 'actor@example.test');
        $officer = $this->admin($role, 'officer@example.test');

        Livewire::actingAs($by, 'web')
            ->test(AdminResetPasswordModal::class)
            ->call('load', $officer->id)
            ->assertSet('resettingAdminEmail', 'officer@example.test')
            ->call('resetPassword')
            ->assertEmitted('user-saved');

        Notification::assertSentTo($officer, ResetPasswordNotification::class);
    }

    // ===== Helpers =====

    /**
     * The link the welcome carried.
     */
    private function linkFor(Admin $admin): string
    {
        $url = null;

        Notification::assertSentTo($admin, OfficerAccountOpened::class, function ($notification) use (&$url) {
            $property = (new \ReflectionClass($notification))->getProperty('url');
            $property->setAccessible(true);
            $url = $property->getValue($notification);

            return true;
        });

        return (string) $url;
    }

    private function role(string $name): Role
    {
        return Role::create([
            'name'        => $name,
            'description' => $name,
            'permissions' => [],
        ]);
    }

    private function admin(Role $role, string $email = 'officer@example.test'): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => $email,
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);
    }
}
