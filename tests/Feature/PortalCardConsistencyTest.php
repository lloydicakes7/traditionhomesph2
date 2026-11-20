<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Http\Livewire\Portal\Complaints\Modals\ComplaintViewModal;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The portal's complaint and violation cards read the same as its request
 * cards: the same three-column shell, header, badge, label/value rows and
 * bottom-right actions.
 */
class PortalCardConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_complaints_use_the_request_card_design_and_keep_long_descriptions(): void
    {
        $user = factory(User::class)->create();
        $description = str_repeat('The household keeps playing loud music after curfew. ', 8);

        $complaint = Complaint::create([
            'resident_id'   => $user->resident_id,
            'submitter_name' => $user->resident->full_name ?? 'Homeowner',
            'category'      => ComplaintCategory::NOISE,
            'subject'       => 'Noise after hours',
            'description'   => $description,
            'status'        => ComplaintStatus::RESOLVED,
            'response'      => 'The office spoke with the household.',
            'responded_at'  => now(),
        ]);

        $this->actingAs($user, 'homeowner')
            ->get(route('portal.complaints'))
            ->assertOk()
            ->assertSee('card d-flex flex-column mb-0 shadow-sm', false)
            ->assertSee('col-12 col-md-6 col-lg-4', false)
            ->assertSee('Noise after hours')
            ->assertSee($description)
            ->assertSee('Category')
            ->assertSee('badge badge-success', false)
            ->assertSee('The office spoke with the household.')
            ->assertSee('title="View complaint"', false)
            ->assertSee('data-target="#complaintViewModal"', false)
            ->assertSee('data-id="' . $complaint->id . '"', false)
            ->assertDontSee('data-target="#complaintEditModal"', false)
            ->assertDontSee('data-target="#complaintDeleteModal"', false);
    }

    public function test_the_complaint_view_modal_shows_only_the_owners_complaint(): void
    {
        $owner = factory(User::class)->create();

        $complaint = Complaint::create([
            'resident_id'    => $owner->resident_id,
            'submitter_name' => $owner->resident->full_name ?? 'Homeowner',
            'category'       => ComplaintCategory::NOISE,
            'subject'        => 'Noise after hours',
            'description'    => 'Loud music past curfew.',
            'status'         => ComplaintStatus::PENDING,
        ]);

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintViewModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', true)
            ->assertSee('Noise after hours')
            ->assertSee('Loud music past curfew.')
            ->assertSee('History');

        $other = factory(User::class)->create();

        Livewire::actingAs($other, 'homeowner')
            ->test(ComplaintViewModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);
    }

    public function test_violation_and_history_cards_share_the_design_and_offer_only_view(): void
    {
        $user = factory(User::class)->create();

        $active = Violation::create([
            'resident_id'    => $user->resident_id,
            'resident_name'  => $user->resident->full_name ?? 'Homeowner',
            'violation_type' => ViolationType::EXCESSIVE_NOISE,
            'occurred_on'    => now()->toDateString(),
            'location'       => 'Block 2 Lot 8',
            'description'    => 'Loud music past curfew.',
            'status'         => ViolationStatus::ACTIVE,
        ]);

        $history = Violation::create([
            'resident_id'    => $user->resident_id,
            'resident_name'  => $user->resident->full_name ?? 'Homeowner',
            'violation_type' => ViolationType::WASTE_DISPOSAL,
            'occurred_on'    => now()->subWeek()->toDateString(),
            'location'       => 'Block 3 Lot 1',
            'description'    => 'Waste left outside the collection day.',
            'status'         => ViolationStatus::RESOLVED,
            'resolved_at'    => now()->subDay(),
        ]);

        $this->actingAs($user, 'homeowner')
            ->get(route('portal.violations'))
            ->assertOk()
            ->assertSee('card d-flex flex-column mb-0 shadow-sm', false)
            ->assertSee('col-12 col-md-6 col-lg-4', false)
            ->assertSee('title="View notice"', false)
            ->assertSee('data-target="#violationViewModal"', false)
            ->assertDontSee('data-target="#violationEditModal"', false)
            ->assertDontSee('data-target="#violationDeleteModal"', false)
            ->assertSee($active->location)
            ->assertSee($history->location)
            ->assertSee('Violation History')
            ->assertSee($history->resolved_at->format('M j, Y'));
    }
}
