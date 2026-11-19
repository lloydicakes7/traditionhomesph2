<?php

namespace Tests\Feature;

use App\Actions\Support\Complaints\DeleteComplaint;
use App\Actions\Support\Complaints\UpdateComplaint;
use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Livewire\Portal\Complaints\Modals\ComplaintDeleteModal;
use App\Http\Livewire\Portal\Complaints\Modals\ComplaintEditModal;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A homeowner may correct or withdraw a complaint until the office acts on it.
 * The card offers the actions only while the complaint is pending and has no
 * office response, and the policy and write actions enforce the same rule.
 */
class PortalComplaintManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pending_complaint_offers_view_edit_and_delete(): void
    {
        $complaint = $this->complaint();

        $this->actingAs($complaint->resident->user, 'homeowner')
            ->get(route('portal.complaints'))
            ->assertOk()
            ->assertSee('data-target="#complaintViewModal"', false)
            ->assertSee('title="Edit complaint"', false)
            ->assertSee('data-target="#complaintEditModal"', false)
            ->assertSee('title="Delete complaint"', false)
            ->assertSee('data-target="#complaintDeleteModal"', false);
    }

    public function test_an_office_handled_complaint_offers_only_view(): void
    {
        $complaint = $this->complaint(ComplaintStatus::IN_PROGRESS);

        $this->actingAs($complaint->resident->user, 'homeowner')
            ->get(route('portal.complaints'))
            ->assertOk()
            ->assertSee('data-target="#complaintViewModal"', false)
            ->assertDontSee('data-target="#complaintEditModal"', false)
            ->assertDontSee('data-target="#complaintDeleteModal"', false);
    }

    public function test_a_responded_complaint_cannot_be_changed_even_while_pending(): void
    {
        $complaint = $this->complaint(ComplaintStatus::PENDING, null, true);
        $owner = $complaint->resident->user;

        $this->assertFalse($owner->can('updateOwn', $complaint));
        $this->assertFalse($owner->can('deleteOwn', $complaint));

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintEditModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintDeleteModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);
    }

    public function test_an_owner_edits_a_pending_complaint(): void
    {
        $complaint = $this->complaint();
        $owner = $complaint->resident->user;

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintEditModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', true)
            ->assertSet('form.subject', 'Noise after hours')
            ->set('form.category', ComplaintCategory::OTHER)
            ->set('form.other_description', 'Construction noise')
            ->set('form.subject', 'Construction noise after hours')
            ->set('form.description', 'Drilling at 11 PM.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('complaint-edited');

        $complaint = $complaint->fresh();

        $this->assertSame(ComplaintCategory::OTHER, $complaint->category->value);
        $this->assertSame('Construction noise', $complaint->other_description);
        $this->assertSame('Construction noise after hours', $complaint->subject);
        $this->assertSame('Drilling at 11 PM.', $complaint->description);
    }

    public function test_an_office_handled_complaint_cannot_be_edited_by_its_owner(): void
    {
        $complaint = $this->complaint(ComplaintStatus::RESOLVED);
        $owner = $complaint->resident->user;

        $this->assertFalse($owner->can('updateOwn', $complaint));

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintEditModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);

        $this->expectException(ValidationException::class);

        app(UpdateComplaint::class)->handle($complaint, [
            'category'    => ComplaintCategory::NOISE,
            'subject'     => 'A rewrite after the office acted',
            'description' => 'This must not be saved.',
        ], null);
    }

    public function test_another_homeowner_cannot_edit_or_delete_the_complaint(): void
    {
        $complaint = $this->complaint();
        $other = factory(User::class)->create();

        $this->assertFalse($other->can('updateOwn', $complaint));
        $this->assertFalse($other->can('deleteOwn', $complaint));

        Livewire::actingAs($other, 'homeowner')
            ->test(ComplaintEditModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);

        Livewire::actingAs($other, 'homeowner')
            ->test(ComplaintDeleteModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);
    }

    public function test_an_owner_deletes_a_pending_complaint(): void
    {
        $complaint = $this->complaint();
        $owner = $complaint->resident->user;

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintDeleteModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', true)
            ->call('delete')
            ->assertEmitted('complaint-deleted');

        $this->assertNull(Complaint::find($complaint->id));
    }

    public function test_an_office_handled_complaint_cannot_be_deleted_by_its_owner(): void
    {
        $complaint = $this->complaint(ComplaintStatus::RESOLVED);
        $owner = $complaint->resident->user;

        $this->assertFalse($owner->can('deleteOwn', $complaint));

        Livewire::actingAs($owner, 'homeowner')
            ->test(ComplaintDeleteModal::class)
            ->call('load', $complaint->id)
            ->assertSet('loaded', false);

        $this->expectException(ValidationException::class);

        app(DeleteComplaint::class)->handle($complaint, null);
    }

    private function complaint(
        string $status = ComplaintStatus::PENDING,
        ?User $owner = null,
        bool $responded = false
    ): Complaint {
        $owner = $owner ?: factory(User::class)->create();

        return Complaint::create([
            'resident_id'    => $owner->resident_id,
            'submitter_name' => $owner->resident->full_name ?? 'Homeowner',
            'category'       => ComplaintCategory::NOISE,
            'subject'        => 'Noise after hours',
            'description'    => 'Loud music past curfew.',
            'status'         => $status,
            'response'       => $responded ? 'The office spoke with the household.' : null,
            'responded_at'   => $responded ? now() : null,
        ]);
    }
}
