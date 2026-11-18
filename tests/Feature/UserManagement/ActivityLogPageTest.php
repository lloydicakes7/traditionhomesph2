<?php

namespace Tests\Feature\UserManagement;

use App\Http\Livewire\Admin\UserManagement\Activity\ActivityList;
use App\Http\Livewire\Admin\UserManagement\Activity\Modals\ActivityMassDeleteModal;
use App\Http\Livewire\Admin\UserManagement\User\Modals\UserActivityDeleteAllModal;
use App\Http\Livewire\Admin\UserManagement\User\Modals\UserActivityDeleteModal;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_users_page_no_longer_carries_the_activity_log(): void
    {
        $this->signInAdministrator();

        $this->get(route('users.index'))
            ->assertOk()
            ->assertSee('New Admin')
            ->assertDontSee('id="activityLogSection"', false);
    }

    public function test_the_user_activities_page_lists_the_trail(): void
    {
        $this->signInAdministrator();

        $this->record('Changed a setting');

        $this->get(route('users.activities'))
            ->assertOk()
            ->assertSee('User Activities')
            ->assertSee('Date/Time')
            ->assertSee('Admin')
            ->assertSee('Action')
            ->assertSee('Module')
            ->assertSee('Description')
            ->assertSee('Officer')
            ->assertSee('Changed a setting')
            ->assertSee('Actions')
            ->assertSee('data-target="#activityDeleteModal"', false);
    }

    public function test_the_page_opens_on_today_from_midnight_to_the_last_minute(): void
    {
        $this->signInAdministrator();

        Livewire::test(ActivityList::class)
            ->assertSet('date', now()->toDateString())
            ->assertSet('fromTime', '00:00')
            ->assertSet('toTime', '23:59');
    }

    public function test_the_trail_is_narrowed_by_the_search_box(): void
    {
        $admin = $this->signInAdministrator();

        $this->record('Posted an invoice', $admin);
        $this->record('Updated a homeowner', $admin);

        Livewire::test(ActivityList::class)
            ->assertSee('Posted an invoice')
            ->assertSee('Updated a homeowner')
            ->set('search', 'invoice')
            ->assertSee('Posted an invoice')
            ->assertDontSee('Updated a homeowner')
            ->set('search', 'nobody')
            ->assertDontSee('Posted an invoice')
            ->assertSee('No activities match these filters.');
    }

    public function test_only_the_chosen_day_and_time_window_are_listed(): void
    {
        $admin = $this->signInAdministrator();

        $morning = $this->record('Morning round', $admin);
        $yesterday = $this->record('Yesterday round', $admin);

        DB::table('admin_activities')->where('id', $morning->id)->update(['created_at' => now()->setTime(8, 30)]);
        DB::table('admin_activities')->where('id', $yesterday->id)->update(['created_at' => now()->subDay()->setTime(8, 30)]);

        Livewire::test(ActivityList::class)
            ->assertSee('Morning round')
            ->assertDontSee('Yesterday round')
            ->set('fromTime', '09:00')
            ->assertDontSee('Morning round')
            ->set('fromTime', '08:00')
            ->set('toTime', '09:00')
            ->assertSee('Morning round');

        Livewire::test(ActivityList::class)
            ->set('date', now()->subDay()->toDateString())
            ->assertSee('Yesterday round')
            ->assertDontSee('Morning round');
    }

    public function test_the_trail_paginates_with_its_own_page_parameter(): void
    {
        $admin = $this->signInAdministrator();

        foreach (range(1, 16) as $number) {
            $this->record('Activity ' . $number, $admin);
        }

        $this->get(route('users.activities', ['activityPage' => 2]))
            ->assertOk()
            ->assertSee('Activity 1')
            ->assertDontSee('Activity 16');
    }

    public function test_an_individual_activity_log_can_be_deleted_without_touching_another(): void
    {
        $admin = $this->signInAdministrator();

        $first = $this->record('First activity', $admin);
        $second = $this->record('Second activity', $admin);

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

    public function test_select_all_covers_every_log_the_filters_keep(): void
    {
        $admin = $this->signInAdministrator();

        $this->record('First activity', $admin);
        $this->record('Second activity', $admin);

        Livewire::test(ActivityList::class)
            ->set('selectAll', true)
            ->assertCount('selectedIds', 2)
            ->set('selectAll', false)
            ->assertSet('selectedIds', []);
    }

    public function test_selected_activity_logs_can_be_deleted_together(): void
    {
        $admin = $this->signInAdministrator();

        $first = $this->record('First activity', $admin);
        $second = $this->record('Second activity', $admin);
        $kept = $this->record('Kept activity', $admin);

        Livewire::test(ActivityMassDeleteModal::class)
            ->call('load', $first->id . ',' . $second->id)
            ->assertSet('selectedIds', [$first->id, $second->id])
            ->assertSee('selected activity logs')
            ->call('deleteMass');

        $this->assertDatabaseMissing('admin_activities', ['id' => $first->id]);
        $this->assertDatabaseMissing('admin_activities', ['id' => $second->id]);
        $this->assertDatabaseHas('admin_activities', ['id' => $kept->id]);
    }

    public function test_all_activity_logs_of_one_admin_can_be_deleted(): void
    {
        $admin = $this->signInAdministrator();

        $this->record('First activity', $admin);
        $this->record('Second activity', $admin);

        Livewire::test(UserActivityDeleteAllModal::class)
            ->call('load', $admin->id)
            ->assertSet('activityCount', 2)
            ->call('delete');

        $this->assertSame(0, AdminActivity::where('admin_id', $admin->id)->count());
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

    private function record(string $description, ?Admin $admin = null): AdminActivity
    {
        return AdminActivity::create([
            'admin_id'    => ($admin ?? auth('web')->user())->id,
            'action'      => 'updated',
            'module'      => 'settings',
            'description' => $description,
        ]);
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
