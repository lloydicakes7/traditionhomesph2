<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Utility\NotificationRetentionHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The notifications table's one-week window after a line is read.
 *
 * A read line leaves the table when its week is over; an unread line stays
 * until its reader has opened or deleted it, however old it is. The nightly
 * command is what applies the window (`notifications:prune`).
 */
class NotificationsRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_line_read_more_than_a_week_ago_is_deleted(): void
    {
        $line = $this->line(now()->subDays(NotificationRetentionHelper::READ_DAYS + 1));

        $this->artisan('notifications:prune')->assertExitCode(0);

        $this->assertNull($line->fresh());
    }

    public function test_a_line_read_inside_the_week_is_kept(): void
    {
        $line = $this->line(now()->subDays(NotificationRetentionHelper::READ_DAYS - 1));

        $this->artisan('notifications:prune')->assertExitCode(0);

        $this->assertNotNull($line->fresh());
    }

    public function test_an_unread_line_is_kept_however_old_it_is(): void
    {
        $line = $this->line(null, now()->subYear());

        $this->artisan('notifications:prune')->assertExitCode(0);

        $this->assertNotNull($line->fresh());
    }

    /**
     * One line on a fresh officer's account, read at the given moment.
     */
    private function line($readAt, $createdAt = null): DatabaseNotification
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'retention-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        return $admin->notifications()->create([
            'id'         => (string) Str::uuid(),
            'type'       => 'App\\Notifications\\RequestFiledNotification',
            'data'       => [
                'type'  => 'request',
                'title' => 'A request was filed',
                'body'  => 'John Doe · Filed from the homeowner record',
                'url'   => '/admin/support/requests',
            ],
            'read_at'    => $readAt,
            'created_at' => $createdAt ?: now(),
            'updated_at' => $createdAt ?: now(),
        ]);
    }
}
