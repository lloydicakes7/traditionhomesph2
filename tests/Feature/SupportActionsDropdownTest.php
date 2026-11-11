<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The Complaints and Violations lists lay their row actions out the way the
 * Requests list does: one "Actions" dropdown per row, the moves inside it
 * following the record rather than the reader.
 *
 * The dropdown is namespaced per device (`$actionKey`) because the desktop row
 * and the mobile card are both on the page and share the same partial — the
 * toggle and the menu it labels must not answer to the same id twice.
 */
class SupportActionsDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_complaints_list_offers_its_moves_in_one_dropdown_per_row(): void
    {
        $admin = $this->signIn();
        $complaint = $this->complaint($admin);

        $response = $this->get(route('support.complaints'));

        $response->assertOk();

        // A dropdown, not a row of loose buttons — once per device.
        $response->assertSee('id="complaintActionsDesktop' . $complaint->id . '"', false);
        $response->assertSee('id="complaintActionsMobile' . $complaint->id . '"', false);
        $response->assertSee('aria-labelledby="complaintActionsDesktop' . $complaint->id . '"', false);

        // The moves the record offers: view, respond, edit, delete.
        $response->assertSee('View Details');
        $response->assertSee('Respond');
        $response->assertSee('Edit');
        $response->assertSee('Delete');

        // Each move still opens its own dialog with the record's id.
        $response->assertSee('data-target="#complaintViewModal" data-id="' . $complaint->id . '"', false);
        $response->assertSee('data-target="#complaintRespondModal" data-id="' . $complaint->id . '"', false);
        $response->assertSee('data-target="#complaintFormModal" data-id="' . $complaint->id . '"', false);
        $response->assertSee('data-target="#complaintDeleteModal" data-id="' . $complaint->id . '"', false);
    }

    public function test_the_violations_list_offers_its_moves_in_one_dropdown_per_row(): void
    {
        $this->signIn();
        $violation = $this->violation();

        $response = $this->get(route('support.violations'));

        $response->assertOk();

        $response->assertSee('id="violationActionsDesktop' . $violation->id . '"', false);
        $response->assertSee('id="violationActionsMobile' . $violation->id . '"', false);

        // View, Print Notice, Edit, Resolve and Delete.
        $response->assertSee('View Notice');
        $response->assertSee('Print Notice');
        $response->assertSee('Edit');
        $response->assertSee('Resolve Violation');
        $response->assertSee('Delete');

        $response->assertSee('data-target="#violationViewModal" data-id="' . $violation->id . '"', false);
        $response->assertSee('data-target="#violationFormModal" data-id="' . $violation->id . '"', false);
        $response->assertSee('data-target="#violationResolveModal" data-id="' . $violation->id . '"', false);
        $response->assertSee('data-target="#violationDeleteModal" data-id="' . $violation->id . '"', false);

        // Print is a plain route to the notice document, not a dialog.
        $response->assertSee(route('support.violations.notice', $violation->id), false);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'support-actions-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function resident(): Resident
    {
        return factory(Resident::class)->create(['surname' => 'Reyes', 'first_name' => 'Ana']);
    }

    /**
     * A complaint the office filed itself, so the row offers every move.
     */
    private function complaint(Admin $admin): Complaint
    {
        $resident = $this->resident();

        return Complaint::create([
            'resident_id'      => $resident->id,
            'filed_by_admin_id' => $admin->id,
            'submitter_name'   => $resident->full_name,
            'category'         => ComplaintCategory::NOISE,
            'subject'          => 'Loud karaoke next door',
            'description'      => 'It runs past midnight on weekends.',
            'status'           => ComplaintStatus::PENDING,
        ]);
    }

    private function violation(): Violation
    {
        $resident = $this->resident();

        return Violation::create([
            'resident_id'    => $resident->id,
            'resident_name'  => $resident->full_name,
            'violation_type' => ViolationType::EXCESSIVE_NOISE,
            'occurred_on'    => now()->toDateString(),
            'location'       => 'Block 1 Lot 1',
            'description'    => 'Loud music past curfew.',
            'status'         => ViolationStatus::ACTIVE,
        ]);
    }
}
