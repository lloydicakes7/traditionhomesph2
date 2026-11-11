<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The User Manual, on both sides.
 *
 * It is a manual rather than a page of hints: front matter, a chapter per
 * tool with its tasks, working by role, and the help chapters a manual ends
 * with. It has its own shell — a navbar without the panel's notification bell,
 * a sidebar of the chapters with the panel's own search box, and a mini table
 * of contents on the right of the active chapter. The admin manual only offers
 * a tool the officer's role can open — the same Policies the panel's own pages
 * answer from — and a chapter the officer may not read is a 404, not a
 * silently-empty page. The portal manual is ownership-based, so every chapter
 * is offered to any signed-in homeowner.
 */
class UserManualTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Who may read it
    // =====================================================================

    public function test_a_guest_cannot_open_either_manual(): void
    {
        $this->get(route('admin.manual'))->assertRedirect(route('login'));
        $this->get(route('portal.manual'))->assertRedirect(route('login'));
    }

    // =====================================================================
    // The admin manual
    // =====================================================================

    public function test_the_administrator_reads_every_admin_tool(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('admin.manual'));

        $response->assertOk();

        // Topics from every group are offered.
        foreach (['Homeowners', 'Collections', 'Accounting', 'Supports', 'Reports', 'User Management', 'Settings'] as $tool) {
            $response->assertSee($tool);
        }

        // It carries its own sidebar and a table of contents.
        $response->assertSee('admin/manual/', false);
        $response->assertSee('nav-header', false);
        $response->assertSee('On this page');
        $response->assertSee('id="section-0"', false);
    }

    public function test_the_manual_is_a_manual_and_not_a_page_of_hints(): void
    {
        $this->signInAdmin();

        // What the system is and how to get into it, then the chapters a
        // manual is expected to end with.
        $response = $this->get(route('admin.manual'));

        foreach (['Introduction', 'Getting Started', 'Troubleshooting', 'FAQ and Tips', 'Glossary', 'Working by Role'] as $chapter) {
            $response->assertSee($chapter);
        }

        // A task states what must be true first, the steps, what a working
        // result looks like, and the mistakes to avoid.
        $this->get(route('admin.manual', ['topic' => 'getting-started']))
            ->assertOk()
            ->assertSee('Before you start')
            ->assertSee('What you should see')
            ->assertSee('Notes');

        // A screen reference names the columns and fields the page prints.
        $this->get(route('admin.manual', ['topic' => 'homeowners']))
            ->assertOk()
            ->assertSee('Screen reference');
    }

    public function test_the_topic_card_carries_no_summary_above_its_sections(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('admin.manual', ['topic' => 'homeowners']));

        $response->assertOk();

        // The chapter's own words are its sections; the title is followed by
        // content, never by a one-line blurb about the chapter.
        $response->assertSee('What the Homeowners area holds');
        $response->assertDontSee('text-muted mb-3', false);
    }

    public function test_the_manual_carries_the_panels_search_box_and_no_notification_bell(): void
    {
        $this->signInAdmin();

        $response = $this->get(route('admin.manual'));

        // The search box the panel's own sidebar publishes, working on the
        // manual's own chapter list — and inside the sidebar, not the navbar,
        // because AdminLTE's own widget searches the menu it sits in.
        $response->assertSee('data-widget="sidebar-search"', false);

        $html = $response->getContent();
        $sidebar = Str::before(Str::after($html, 'class="main-sidebar'), '</aside>');

        $this->assertStringContainsString('nav-sidebar', $sidebar);
        $this->assertStringContainsString('data-widget="sidebar-search"', $sidebar);

        // A reading page carries no notifications, so its navbar paints none...
        $response->assertDontSee('notif-bell');

        // ...while the panel itself still does.
        $this->assertTrue(collect(config('adminlte.menu'))->contains('type', 'navbar-notification'));
    }

    public function test_the_manual_offers_only_the_tools_the_role_can_open(): void
    {
        // An officer granted one page — Addresses — and nothing else.
        $this->signInAdmin('Front Desk', ['addresses.view']);

        $response = $this->get(route('admin.manual'));

        $response->assertOk();

        // The homeowners tool is offered, because Addresses lives under it.
        $response->assertSee('Homeowners');

        // The finance and administration tools are closed, so the manual
        // never names them — not even in the front matter it does offer.
        $response->assertDontSee('Collections');
        $response->assertDontSee('Accounting');
        $response->assertDontSee('User Management');

        // Opening the tool it is offered reads its own sections.
        $this->get(route('admin.manual', ['topic' => 'homeowners']))
            ->assertOk()
            ->assertSee('Addresses');

        // A topic the role may not open is a 404, not an empty page.
        $this->get(route('admin.manual', ['topic' => 'collections']))->assertNotFound();
    }

    public function test_an_unknown_topic_is_a_404(): void
    {
        $this->signInAdmin();

        $this->get(route('admin.manual', ['topic' => 'not-a-tool']))->assertNotFound();
    }

    // =====================================================================
    // The portal manual
    // =====================================================================

    public function test_the_homeowner_reads_the_portal_manual(): void
    {
        $homeowner = factory(User::class)->create();

        $response = $this->actingAs($homeowner, 'homeowner')->get(route('portal.manual'));

        $response->assertOk();

        foreach (['My Bookings', 'Requests', 'Complaints', 'Violations', 'Statement of Account', 'My Payments', 'Household'] as $page) {
            $response->assertSee($page);
        }

        // It is the portal's own manual: no admin route leaks into it.
        $response->assertDontSee('admin/manual', false);

        // A named topic opens its own page.
        $this->actingAs($homeowner, 'homeowner')
            ->get(route('portal.manual', ['topic' => 'payments']))
            ->assertOk()
            ->assertSee('Reading your receipts');
    }

    public function test_the_portal_manual_carries_the_search_box_and_no_notification_bell(): void
    {
        $homeowner = factory(User::class)->create();

        $response = $this->actingAs($homeowner, 'homeowner')->get(route('portal.manual'));

        $response->assertSee('data-widget="sidebar-search"', false);
        $response->assertDontSee('portal-notif-bell');

        // The portal itself still carries its bell.
        $this->assertSame('portal-notif-bell', config('portal.notification.id'));

        // It reads as a manual too: front matter and the help chapters.
        foreach (['Introduction', 'Getting Started', 'Troubleshooting', 'FAQ and Tips', 'Glossary'] as $chapter) {
            $response->assertSee($chapter);
        }
    }

    // =====================================================================
    // Reading in Filipino
    // =====================================================================

    public function test_the_admin_manual_can_be_read_in_filipino(): void
    {
        $this->signInAdmin();

        // The navbar's selector offers both languages by their short name.
        $this->get(route('admin.manual'))
            ->assertOk()
            ->assertSee('manual-locale', false)
            ->assertSee('>EN<', false)
            ->assertSee('>FIL<', false);

        // Choosing Filipino reloads the same chapter, in Filipino.
        $this->get(route('admin.manual', ['topic' => 'homeowners', 'lang' => 'fil']))
            ->assertOk()
            ->assertSee('Manwal ng Gumagamit')
            ->assertSee('Mga Homeowner')
            ->assertSee('Bago ka magsimula');

        // The choice is remembered for the pages that carry no query string.
        $this->get(route('admin.manual', ['topic' => 'homeowners']))
            ->assertOk()
            ->assertSee('Manwal ng Gumagamit');
    }

    public function test_an_unsupported_language_falls_back_to_the_default(): void
    {
        $this->signInAdmin();

        $this->get(route('admin.manual', ['lang' => 'de']))
            ->assertOk()
            ->assertSee('User Manual')
            ->assertDontSee('Manwal ng Gumagamit');
    }

    public function test_the_portal_manual_can_be_read_in_filipino(): void
    {
        $homeowner = factory(User::class)->create();

        $this->actingAs($homeowner, 'homeowner')
            ->get(route('portal.manual', ['topic' => 'payments', 'lang' => 'fil']))
            ->assertOk()
            ->assertSee('Aking mga Bayad')
            ->assertSee('Pagbasa ng iyong mga resibo');

        // It is still the portal's own manual: no admin route leaks into it.
        $this->assertSame('fil', session('manual.locale'));
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

    /**
     * Sign in an officer holding a role of the given name and permissions.
     *
     * @param  array<int, string>  $permissions  The permission keys the role holds
     */
    private function signInAdmin(string $role = 'Administrator', array $permissions = []): void
    {
        $role = Role::create([
            'name'        => $role,
            'description' => $role,
            'permissions' => $permissions,
        ]);

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'manual-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
