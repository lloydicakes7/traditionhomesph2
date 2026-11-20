<?php

namespace Tests\Feature;

use App\Enums\ViolationType;
use App\Http\Livewire\Admin\Support\Violations\Modals\ViolationFormModal;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ViolationTypeOtherTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_form_shows_the_custom_type_only_while_other_is_selected(): void
    {
        $this->signIn();
        $this->resident();

        Livewire::test(ViolationFormModal::class)
            ->call('openCreate')
            ->assertDontSeeHtml('name="form.violation_type_other"')
            ->set('form.violation_type', ViolationType::OTHER)
            ->assertSeeHtml('name="form.violation_type_other"')
            ->set('form.violation_type', ViolationType::EXCESSIVE_NOISE)
            ->assertDontSeeHtml('name="form.violation_type_other"');
    }

    public function test_a_custom_type_is_required_and_saved_when_other_is_selected(): void
    {
        $admin = $this->signIn();
        $resident = $this->resident();

        Livewire::test(ViolationFormModal::class)
            ->call('openCreate')
            ->set('form.resident_id', $resident->id)
            ->set('form.violation_type', ViolationType::OTHER)
            ->set('form.occurred_on', now()->toDateString())
            ->set('form.occurred_time', '20:30')
            ->set('form.location', 'Block 4 open lot')
            ->set('form.description', 'Livestock kept inside the unit.')
            ->call('save')
            ->assertHasErrors(['form.violation_type_other' => 'required_if']);

        Livewire::test(ViolationFormModal::class)
            ->call('openCreate')
            ->set('form.resident_id', $resident->id)
            ->set('form.violation_type', ViolationType::OTHER)
            ->set('form.violation_type_other', 'Keeping livestock in the unit')
            ->set('form.occurred_on', now()->toDateString())
            ->set('form.occurred_time', '20:30')
            ->set('form.location', 'Block 4 open lot')
            ->set('form.description', 'Livestock kept inside the unit.')
            ->call('save')
            ->assertHasNoErrors();

        $violation = Violation::firstOrFail();

        $this->assertSame('Keeping livestock in the unit', $violation->violation_type_other);
        $this->assertSame('Keeping livestock in the unit', $violation->type_label);
        $this->assertSame($admin->id, (int) $violation->issued_by_admin_id);
    }

    public function test_a_predefined_type_carries_no_custom_description(): void
    {
        $this->signIn();
        $resident = $this->resident();

        Livewire::test(ViolationFormModal::class)
            ->call('openCreate')
            ->set('form.resident_id', $resident->id)
            ->set('form.violation_type', ViolationType::EXCESSIVE_NOISE)
            ->set('form.violation_type_other', 'Stale description')
            ->set('form.occurred_on', now()->toDateString())
            ->set('form.occurred_time', '21:00')
            ->set('form.location', 'Block 1 Lot 1')
            ->set('form.description', 'Loud music past curfew.')
            ->call('save')
            ->assertHasNoErrors();

        $violation = Violation::firstOrFail();

        $this->assertNull($violation->violation_type_other);
        $this->assertSame('Excessive Noise', $violation->type_label);
    }

    public function test_the_portal_offers_no_acknowledgement(): void
    {
        $owner = factory(User::class)->create();

        Violation::create([
            'resident_id'          => $owner->resident_id,
            'resident_name'        => 'Ana Reyes',
            'violation_type'       => ViolationType::OTHER,
            'violation_type_other' => 'Keeping livestock in the unit',
            'occurred_on'          => now()->toDateString(),
            'location'             => 'Block 1 Lot 1',
            'description'          => 'Livestock kept inside the unit.',
            'status'               => 'active',
        ]);

        $this->actingAs($owner, 'homeowner')
            ->get(route('portal.violations'))
            ->assertOk()
            ->assertSee('Keeping livestock in the unit')
            ->assertSee('card d-flex flex-column mb-0 shadow-sm', false)
            ->assertSee('col-12 col-md-6 col-lg-4', false)
            ->assertSee('title="View notice"', false)
            ->assertSee('data-target="#violationViewModal"', false)
            ->assertDontSee('data-target="#violationEditModal"', false)
            ->assertDontSee('data-target="#violationDeleteModal"', false)
            ->assertDontSee('Acknowledge');
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
            'name'      => 'Officer',
            'email'     => 'violation-other-' . uniqid() . '@example.test',
            'password'  => Hash::make('Secret123!'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function resident(): Resident
    {
        return factory(Resident::class)->create(['surname' => 'Reyes', 'first_name' => 'Ana']);
    }
}
