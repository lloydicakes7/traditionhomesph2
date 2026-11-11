<?php

namespace Tests\Feature\Public;

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * One sign-in screen: the email decides which panel opens, and a refusal says
 * the same thing whatever the address belongs to.
 */
class UnifiedAuthTest extends TestCase
{
    use RefreshDatabase;

    // ===== Screen =====

    public function test_the_unified_login_screen_is_public(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign In');
    }

    // ===== Sign in =====

    public function test_an_officer_signs_in_through_the_unified_form(): void
    {
        $admin = $this->admin();

        $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertGuest('homeowner');
    }

    public function test_a_homeowner_signs_in_through_the_unified_form(): void
    {
        $user = factory(User::class)->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertRedirect('/portal');

        $this->assertAuthenticatedAs($user, 'homeowner');
        $this->assertGuest('web');
    }

    public function test_an_officer_signs_in_from_a_browser_holding_a_portal_session(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner');

        $this->post('/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'web');
        $this->assertGuest('homeowner');
    }

    public function test_a_homeowner_signs_in_from_a_browser_holding_an_officer_session(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();

        $this->actingAs($admin, 'web');

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertRedirect('/portal');

        $this->assertAuthenticatedAs($user, 'homeowner');
        $this->assertGuest('web');
    }

    public function test_a_signed_in_officer_is_sent_to_the_panel_from_the_login_screen(): void
    {
        $this->actingAs($this->admin(), 'web')->get('/login')->assertRedirect('/admin');
    }

    public function test_a_signed_in_homeowner_is_sent_to_the_portal_from_the_login_screen(): void
    {
        $this->actingAs(factory(User::class)->create(), 'homeowner')->get('/login')->assertRedirect('/portal');
    }

    public function test_a_refused_sign_in_says_the_same_thing_for_every_account(): void
    {
        $admin = $this->admin();
        $user = factory(User::class)->create();

        $this->from('/login')->post('/login', [
            'email'    => $admin->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->from('/login')->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email' => trans('auth.failed')]);

        $this->from('/login')->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors(['email' => trans('auth.failed')]);
    }

    public function test_a_refused_sign_in_shows_the_message_on_the_screen(): void
    {
        $response = $this->from('/login')->followingRedirects()->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSee('alert alert-danger');
        $this->assertSame(1, substr_count($response->getContent(), trans('auth.failed')));
    }

    public function test_a_locked_out_visitor_is_told_how_long_to_wait(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'email'    => 'nobody@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->from('/login')->followingRedirects()->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'wrong-password',
        ])->assertSee(explode(':seconds', trans('auth.throttle'))[0]);
    }

    public function test_a_pending_homeowner_cannot_sign_in_and_sees_the_pending_notice(): void
    {
        $user = factory(User::class)->create(['is_active' => false]);

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('registration.pending'));

        $this->assertGuest('homeowner');

        $this->get('/pending-approval')
            ->assertOk()
            ->assertSee(trans('auth.pending_title'));
    }

    // ===== Password confirmation =====

    public function test_an_officer_confirms_their_password_before_a_protected_action(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'web')
            ->get('/admin/password/confirm')
            ->assertOk();

        $this->actingAs($admin, 'web')
            ->from('/admin/password/confirm')
            ->post('/admin/password/confirm', ['password' => 'wrong-password'])
            ->assertRedirect('/admin/password/confirm')
            ->assertSessionHasErrors('password');

        $this->actingAs($admin, 'web')
            ->post('/admin/password/confirm', ['password' => 'password'])
            ->assertRedirect('/admin');

        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    public function test_a_homeowner_confirms_their_password_before_a_protected_action(): void
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner')
            ->get('/portal/password/confirm')
            ->assertOk();

        $this->actingAs($user, 'homeowner')
            ->post('/portal/password/confirm', ['password' => 'password'])
            ->assertRedirect('/portal');
    }

    // ===== Removed screens =====

    public function test_the_forced_password_change_screen_no_longer_exists(): void
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner')->get('/portal/password/change')->assertNotFound();
    }

    // ===== Sign out =====

    public function test_sign_out_clears_the_session_and_returns_to_the_login_screen(): void
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner')
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest('homeowner');
    }

    // ===== Tools =====

    private function admin(): Admin
    {
        $role = Role::create(['name' => DefaultRoleSlug::ADMINISTRATOR]);

        return Admin::create([
            'name'      => 'Test Officer',
            'email'     => 'officer@example.com',
            'password'  => Hash::make('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }
}
