<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The panel's sidebar — the one list every officer reads before any page.
 *
 * Two rules are pinned here, both about what the sidebar owes the officer
 * reading it. It publishes no Navigation page: the menu's own search box finds
 * a page by name, so a second, hand-kept list of every page only goes stale.
 * And it never prints a section title with nothing under it — the package drops
 * what a role may not open, and an emptied submenu with it, but a title is
 * nobody's permission, so "FINANCE" would otherwise sit over nothing for an
 * officer the finance pages are closed to.
 */
class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // The pages it offers
    // =====================================================================

    public function test_the_panel_publishes_no_navigation_page(): void
    {
        $this->signIn('Administrator');

        $response = $this->get(route('admin.home'));

        $response->assertOk();
        $response->assertDontSee('admin/navigation');
        $this->assertFalse(Route::has('admin.navigation'));
    }

    // =====================================================================
    // The titles it prints
    // =====================================================================

    public function test_a_title_whose_section_the_role_emptied_is_not_printed(): void
    {
        // An officer granted one page — Addresses — and nothing else.
        $this->signIn('Front Desk', ['addresses.view']);

        $response = $this->get(route('admin.home'));

        $response->assertOk();

        // The page they may open, and the title standing over it.
        $response->assertSee('Addresses');
        $response->assertSee('OPERATIONS');

        // Both closed sections lost their titles with their pages.
        $response->assertDontSee('FINANCE');
        $response->assertDontSee('REPORTS AND SUPPORT');

        // What is left is exactly the two sections that kept something:
        // OPERATIONS (via Homeowners) and ADMINISTRATION (its User Manual line
        // is granted to everyone).
        $this->assertSame(2, substr_count($response->getContent(), 'nav-header'));
    }

    public function test_the_administrator_still_reads_every_title(): void
    {
        $this->signIn('Administrator');

        $response = $this->get(route('admin.home'));

        $response->assertOk();

        foreach (['OPERATIONS', 'FINANCE', 'REPORTS AND SUPPORT', 'ADMINISTRATION'] as $title) {
            $response->assertSee($title);
        }
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

    /**
     * Sign in an officer holding a role of the given name and permissions.
     *
     * @param  array<int, string>  $permissions  The permission keys the role holds
     */
    private function signIn(string $role, array $permissions = []): void
    {
        $role = Role::create([
            'name'        => $role,
            'description' => $role,
            'permissions' => $permissions,
        ]);

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'sidebar-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
