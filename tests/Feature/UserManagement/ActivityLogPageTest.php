<?php

namespace Tests\Feature\UserManagement;

use App\Http\Livewire\Admin\UserManagement\Activity\ActivityList;
use App\Http\Livewire\Admin\UserManagement\Activity\Modals\DeleteAllActivitiesModal;
use App\Http\Livewire\Admin\UserManagement\User\Modals\UserActivityDeleteModal;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_users_page_lists_activity_logs_below_the_admin_list(): void
    {
        $this->signInAdministrator();

        $activity = AdminActivity::create([
            'admin_id'    => auth('web')->id(),
            'action'      => 'updated',
            'module'      => 'settings',
            'description' => 'Changed a setting',
        ]);

        $response = $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('Activity Log')
            ->assertSee('Delete All Logs')
            ->assertSee('Changed a setting')
            ->assertSee('data-target="#activityDeleteModal"', false)
            ->assertSee('data-id="' . $activity->id . '"', false);

        $html = $response->getContent();

        $this->assertLessThan(
            strpos($html, 'id="activityLogSection"'),
            strpos($html, 'New Admin')
        );
    }

    public function test_the_activity_log_paginates_with_its_own_page_parameter(): void
    {
        $this->signInAdministrator();

        foreach (range(1, 16) as $number) {
            AdminActivity::create([
                'admin_id'    => auth('web')->id(),
                'action'      => 'updated',
                'module'      => 'settings',
                'description' => 'Activity ' . $number,
            ]);
        }

        $this->get(route('users.index', ['activityPage' => 2]))
            ->assertOk()
            ->assertSee('Activity 1')
            ->assertDontSee('Activity 16');
    }

    public function test_an_individual_activity_log_can_be_deleted_without_touching_another(): void
    {
        $this->signInAdministrator();

        $first = AdminActivity::create([
            'admin_id'    => auth('web')->id(),
            'action'      => 'created',
            'module'      => 'invoice',
            'description' => 'First activity',
        ]);

        $second = AdminActivity::create([
            'admin_id'    => auth('web')->id(),
            'action'      => 'updated',
            'module'      => 'settings',
            'description' => 'Second activity',
        ]);

        Livewire::test(UserActivityDeleteModal::class)
            ->call('load', $first->id)
            ->assertSee('First activity')
            ->call('delete');

        $this->assertDatabaseMissing('admin_activities', ['id' => $first->id]);
        $this->assertDatabaseHas('admin_activities', ['id' => $second->id]);

        Livewire::test(ActivityList::class)
            ->assertSee('Second activity')
            ->assertDontSee('First activity');
    }

    public function test_delete_all_removes_only_activity_logs(): void
    {
        $admin = $this->signInAdministrator();
        $role = $admin->role;

        AdminActivity::create([
            'admin_id'    => $admin->id,
            'action'      => 'created',
            'module'      => 'invoice',
            'description' => 'First activity',
        ]);

        AdminActivity::create([
            'admin_id'    => $admin->id,
            'action'      => 'updated',
            'module'      => 'settings',
            'description' => 'Second activity',
        ]);

        Livewire::test(DeleteAllActivitiesModal::class)
            ->call('open')
            ->assertSet('activityCount', 2)
            ->assertSee('Delete All Logs')
            ->call('deleteAll')
            ->assertDispatchedBrowserEvent('modal-close');

        $this->assertSame(0, AdminActivity::query()->count());
        $this->assertDatabaseHas('admins', ['id' => $admin->id]);
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_only_administrators_can_view_and_delete_activity_logs(): void
    {
        $role = Role::create([
            'name'        => 'Visitor',
            'description' => 'Visitor',
            'permissions' => [],
        ]);

        $visitor = Admin::create([
            'name'                => 'Visitor',
            'email'               => 'visitor-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->assertFalse($visitor->can('viewAny', AdminActivity::class));
        $this->assertFalse($visitor->can('delete', AdminActivity::class));
    }

    private function signInAdministrator(): Admin
    {
        $role = Role::create([
            'name'        => 'Administrator',
            'description' => 'Administrator',
            'permissions' => [],
        ]);

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'activity-log-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
