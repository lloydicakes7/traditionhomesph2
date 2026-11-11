<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Http\Livewire\Portal\Requests\Modals\RequestWithdrawModal;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A homeowner may delete their own request while it is still pending. Once the
 * office approves it, the filing is no longer theirs to remove.
 */
class PortalRequestWithdrawTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pending_request_is_deleted_from_the_portal(): void
    {
        $request = $this->request(RequestStatus::PENDING);
        $owner = $request->resident->user;

        Livewire::actingAs($owner, 'homeowner')
            ->test(RequestWithdrawModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', true)
            ->call('withdraw')
            ->assertEmitted('request-withdrawn');

        $this->assertNull(RequestModel::find($request->id));
    }

    public function test_the_requests_page_offers_delete_only_while_pending(): void
    {
        $request = $this->request(RequestStatus::PENDING);
        $owner = $request->resident->user;

        $this->actingAs($owner, 'homeowner')
            ->get(route('portal.requests'))
            ->assertOk()
            ->assertSee('data-target="#requestWithdrawModal"', false)
            ->assertSee('id="requestWithdrawModal"', false);

        $request->update(['status' => RequestStatus::APPROVED]);

        $this->actingAs($owner, 'homeowner')
            ->get(route('portal.requests'))
            ->assertOk()
            ->assertDontSee('data-target="#requestWithdrawModal"', false);
    }

    public function test_an_approved_request_cannot_be_deleted(): void
    {
        $request = $this->request(RequestStatus::APPROVED);
        $owner = $request->resident->user;

        $this->assertFalse($owner->can('deleteOwn', $request));

        Livewire::actingAs($owner, 'homeowner')
            ->test(RequestWithdrawModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', false);

        $this->assertNotNull(RequestModel::find($request->id));
    }

    public function test_another_homeowners_pending_request_cannot_be_deleted(): void
    {
        $request = $this->request(RequestStatus::PENDING);
        $other = factory(User::class)->create();

        $this->assertFalse($other->can('deleteOwn', $request));

        Livewire::actingAs($other, 'homeowner')
            ->test(RequestWithdrawModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', false);

        $this->assertNotNull(RequestModel::find($request->id));
    }

    private function request(string $status): RequestModel
    {
        $owner = factory(User::class)->create();

        return RequestModel::create([
            'resident_id' => $owner->resident_id,
            'type'        => RequestType::CERTIFICATION,
            'purpose'     => 'residency',
            'status'      => $status,
        ]);
    }
}
