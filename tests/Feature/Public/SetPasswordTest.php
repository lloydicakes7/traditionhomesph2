<?php

namespace Tests\Feature\Public;

use App\Actions\Homeowners\Users\CreateUserAccount;
use App\Actions\Homeowners\Users\ResetHomeownerPassword;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The office never sees a homeowner's password: it opens an unusable one and
 * mails a signed set-password link. Reset does the same and kills the old one.
 */
class SetPasswordTest extends TestCase
{
    use RefreshDatabase;

    // ===== Create =====

    public function test_a_new_account_gets_an_unusable_password_and_a_set_password_link(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $resident = factory(Resident::class)->create();

        $user = app(CreateUserAccount::class)->handle([
            'resident_id' => $resident->id,
            'email'       => 'new.homeowner@example.com',
            'is_active'   => true,
        ], $admin);

        $this->assertFalse(Hash::check('', $user->password));
        $this->assertGreaterThan(20, strlen($user->password));

        Notification::assertSentTo($user, SetPasswordNotification::class);
    }

    public function test_the_set_password_link_lands_on_the_screen_and_sets_the_password(): void
    {
        Notification::fake();

        $user = $this->account();

        app(\App\Services\Auth\PasswordSetupService::class)->send($user);

        $url = $this->linkFor($user);

        $this->get($url)->assertOk()->assertSee(trans('auth.set_password_title'));

        $token = $this->tokenFor($url);

        $this->post('/password/set', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'ChosenPass1!',
            'password_confirmation' => 'ChosenPass1!',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('ChosenPass1!', $user->fresh()->password));

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'ChosenPass1!',
        ])->assertRedirect('/portal');
    }

    public function test_a_tampered_set_password_link_is_refused(): void
    {
        Notification::fake();

        $user = $this->account();

        app(\App\Services\Auth\PasswordSetupService::class)->send($user);

        $this->get($this->linkFor($user) . '&signature=forged')->assertForbidden();
    }

    // ===== Reset =====

    public function test_resetting_the_password_invalidates_the_old_one_and_mails_a_new_link(): void
    {
        Notification::fake();

        $admin = $this->admin();
        $user = $this->account();

        $user->forceFill(['password' => Hash::make('OldSecret1!')])->save();

        app(ResetHomeownerPassword::class)->handle($user->id, $admin);

        $this->assertFalse(Hash::check('OldSecret1!', $user->fresh()->password));
        Notification::assertSentTo($user, SetPasswordNotification::class);

        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'reset_password',
            'module'   => 'user',
        ]);
    }

    // ===== Tools =====

    private function account(): User
    {
        return User::create([
            'email'     => 'homeowner@example.com',
            'password'  => Hash::make('Unusable1!'),
            'is_active' => true,
        ]);
    }

    private function admin(): Admin
    {
        $role = Role::create([
            'name'        => 'Administrator',
            'description' => 'Administrator',
            'permissions' => [],
        ]);

        return Admin::create([
            'name'      => 'Officer',
            'email'     => 'officer@example.com',
            'password'  => Hash::make('Secret123!'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }

    /**
     * The signed URL the notification carried.
     */
    private function linkFor(User $user): string
    {
        $url = null;

        Notification::assertSentTo($user, SetPasswordNotification::class, function ($notification) use (&$url) {
            $property = (new \ReflectionClass($notification))->getProperty('url');
            $property->setAccessible(true);
            $url = $property->getValue($notification);

            return true;
        });

        return (string) $url;
    }

    private function tokenFor(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return basename($path);
    }
}
