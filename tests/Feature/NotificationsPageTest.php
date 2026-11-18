<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Notifications\NotificationList;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The notifications page, as both panels read it: the lines' own measure, the
 * pager at the foot of the list, and the read-all action pinned to the header.
 *
 * The page is a Livewire list (`Admin/Client\Notifications\NotificationList`),
 * so its paging and mark-all-as-read navigate the component rather than the
 * page; the bell's polling feed stays on the controller.
 *
 * The page draws its own table of Title, Description and Actions, which
 * collapses into cards on a small screen; the bell keeps the smaller shared
 * line (resources/views/notifications/item).
 * Every account is created with its welcome line already in the bell (see
 * App\Observers\AdminObserver and UserObserver), which is why a fresh officer
 * reads one line before a test adds any.
 */
class NotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    /** How many lines the pager puts on a page (NotificationController::PER_PAGE). */
    const PER_PAGE = 20;

    // =====================================================================
    // The pager
    // =====================================================================

    public function test_the_pager_is_drawn_even_when_the_inbox_fits_one_page(): void
    {
        $this->signInAdmin();

        $this->line(1);

        $response = $this->get(route('admin.notifications'));

        $response->assertOk();
        $response->assertSee('class="pagination"', false);

        // One page: the control is there, and its arrows say there is nowhere
        // to go rather than the whole footer going missing.
        $response->assertSee('page-item disabled', false);
        $response->assertSee('class="page-item active"', false);
    }

    public function test_the_portal_draws_the_same_pager_for_a_households_first_line(): void
    {
        // The account's own welcome line is the only one a new household has.
        $account = $this->portalAccount();

        $response = $this->actingAs($account, 'homeowner')->get(route('portal.notifications'));

        $response->assertOk();
        $response->assertSee('class="pagination"', false);
        $response->assertSee('page-item disabled', false);
    }

    public function test_the_pager_pages_a_long_inbox(): void
    {
        $this->signInAdmin();

        for ($i = 1; $i <= self::PER_PAGE + 5; $i++) {
            $this->line($i);
        }

        // The list is newest first, so the first page holds the newest
        // PER_PAGE lines and the second holds the rest.
        $first = $this->get(route('admin.notifications'));

        $first->assertOk();
        $first->assertSee('Line ' . (self::PER_PAGE + 5) . ' — Request filed');
        $first->assertDontSee('Line 1 — Request filed');

        // The control to page 2 is the component's own Livewire action.
        $first->assertSee('gotoPage(2', false);

        $second = $this->get(route('admin.notifications', ['page' => 2]));

        $second->assertOk();
        $second->assertSee('Line 1 — Request filed');
        $second->assertDontSee('Line ' . (self::PER_PAGE + 5) . ' — Request filed');
    }

    public function test_an_empty_inbox_shows_no_pager(): void
    {
        $admin = $this->signInAdmin();

        // An inbox with nothing in it is the one list with no pager: there is
        // nothing to page, and the card's own empty state says so.
        $admin->notifications()->delete();

        $response = $this->get(route('admin.notifications'));

        $response->assertOk();
        $response->assertDontSee('class="pagination"', false);
        $response->assertSee('Nothing here yet');
    }

    // =====================================================================
    // The line's measure
    // =====================================================================

    public function test_the_page_lists_a_title_a_description_and_its_own_actions(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('admin.notifications'));

        $response->assertOk();

        // The page has its own table: Title, Description and Actions, on a
        // desktop, and the same three fields as cards on a small screen.
        $response->assertSee('<th class="align-middle" style="width: 30%;">Title</th>', false);
        $response->assertSee('<th class="align-middle">Description</th>', false);
        $response->assertSee('>Actions</th>', false);
        $response->assertSee('d-none d-lg-block', false);
        $response->assertSee('d-block d-lg-none', false);
        $response->assertSee('notification-row-', false);
    }

    // =====================================================================
    // The read-all action
    // =====================================================================

    public function test_marking_everything_read_is_pinned_to_the_right_of_the_header(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('admin.notifications'));

        $response->assertOk();

        // The header is a clearfix, so its `::after` is a third flex item:
        // keeping the two ends apart spaces the button around it, which lands
        // it in the middle of the header. The class list therefore ends at
        // `align-items-center`, and a margin of the button's own puts it in the
        // corner.
        $response->assertSee('card-header bg-white d-flex align-items-center"', false);
        $response->assertSee('ml-auto', false);
    }

    public function test_marking_everything_read_is_offered_only_while_something_is_unread(): void
    {
        $admin = $this->signInAdmin();

        $this->get(route('admin.notifications'))->assertSee('Mark all as read');

        $admin->notifications()->update(['read_at' => now()]);

        $this->get(route('admin.notifications'))->assertDontSee('Mark all as read');
    }

    public function test_marking_everything_read_marks_the_accounts_unread_lines(): void
    {
        $admin = $this->signInAdmin();

        $this->line(1);

        $this->assertGreaterThan(0, $admin->unreadNotifications()->count());

        // The action is the component's own: it marks the account's unread rows
        // read and re-renders, which is what hides the button.
        Livewire::test(NotificationList::class)
            ->call('markAllAsRead')
            ->assertEmitted('notify', 'success', 'All notifications marked as read.')
            ->assertDontSee('Mark all as read');

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    // =====================================================================
    // The line's own actions and its deadline
    // =====================================================================

    public function test_a_read_line_says_the_moment_it_will_be_deleted(): void
    {
        $admin = $this->signInAdmin();

        $admin->notifications()->update(['read_at' => now()]);

        $readAt = $admin->notifications()->first()->read_at;
        $deletesAt = $readAt->copy()->addDays(7);

        $response = $this->get(route('admin.notifications'));

        $response->assertOk();
        $response->assertSee('Will be deleted on ' . $deletesAt->format('M j, Y')
            . ' at ' . $deletesAt->format('g:i A'));

        // The deadline is under the description, so the page does not repeat
        // the week-long rule above the list.
        $response->assertDontSee('Read notifications are deleted automatically');
        $response->assertSee('dropdown-item disabled', false);
    }

    public function test_an_unread_line_offers_read_and_delete_and_no_deadline(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('admin.notifications'));

        $response->assertOk();
        $response->assertSee('markAsRead(', false);
        $response->assertSee('fa-check mr-1"></i> Read', false);
        $response->assertSee('fa-trash mr-1"></i> Delete', false);
        $response->assertDontSee('Will be deleted on');
    }

    public function test_one_line_can_be_marked_read_from_its_own_button(): void
    {
        $admin = $this->signInAdmin();

        $this->line(1);

        $line = $admin->notifications()->latest('created_at')->first();

        $expiresAt = now()->addDays(7);

        Livewire::test(NotificationList::class)
            ->call('markAsRead', $line->id)
            ->assertEmitted('notify', 'success', 'Notification marked as read. It will be deleted on '
                . $expiresAt->format('M j, Y') . ' at ' . $expiresAt->format('g:i A') . '.')
            ->assertSee('Will be deleted on');

        $this->assertNotNull($line->fresh()->read_at);
    }

    public function test_one_line_can_be_deleted_from_its_own_button(): void
    {
        $admin = $this->signInAdmin();

        $this->line(1);

        $line = $admin->notifications()->latest('created_at')->first();

        Livewire::test(NotificationList::class)
            ->call('delete', $line->id)
            ->assertEmitted('notify', 'success', 'Notification deleted.')
            ->assertDontSee($line->data['title']);

        $this->assertNull($line->fresh());
    }

    public function test_another_accounts_line_can_be_neither_read_nor_deleted(): void
    {
        $this->signInAdmin();

        $other = Admin::create([
            'name'                => 'Other Officer',
            'email'               => 'other-officer-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => Role::first()->id,
            'is_active'           => true,
        ]);

        $line = $other->notifications()->first();

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(NotificationList::class)->call('delete', $line->id);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * One unread notification on the signed-in officer's account, numbered so
     * a test can tell one page of them from the next.
     */
    private function line(int $number): void
    {
        $admin = Admin::query()->first();

        $admin->notifications()->create([
            'id'         => (string) Str::uuid(),
            'type'       => 'App\\Notifications\\RequestFiledNotification',
            'data'       => [
                'type'  => 'request',
                'title' => 'Line ' . $number . ' — Request filed',
                'body'  => 'John Doe · Filled in from the homeowner record',
                'url'   => '/admin/support/requests',
            ],
            'read_at'    => null,
            'created_at' => now()->addSeconds($number),
            'updated_at' => now()->addSeconds($number),
        ]);
    }

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'notifications-page-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function portalAccount(): User
    {
        $resident = factory(Resident::class)->create();

        return factory(User::class)->create(['resident_id' => $resident->id]);
    }
}
