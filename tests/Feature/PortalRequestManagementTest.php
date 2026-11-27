<?php

namespace Tests\Feature;

use App\Actions\Support\Requests\UpdateRequest;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestApproveModal;
use App\Http\Livewire\Portal\Requests\Modals\RequestEditModal;
use App\Http\Livewire\Portal\Requests\Modals\RequestViewModal;
use App\Models\Admin;
use App\Models\Request as RequestModel;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The portal's own handling of a request: reading it, correcting it while the
 * office has not decided, and removing it — plus the claiming schedule the
 * officer names when approving.
 */
class PortalRequestManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pending_request_offers_view_edit_and_delete_on_the_card(): void
    {
        $request = $this->request();

        $this->actingAs($request->resident->user, 'homeowner')
            ->get(route('portal.requests'))
            ->assertOk()
            ->assertSee('data-target="#requestViewModal"', false)
            ->assertSee('data-target="#requestEditModal"', false)
            ->assertSee('data-target="#requestWithdrawModal"', false);
    }

    public function test_the_page_opens_the_request_form_for_the_document_in_the_link(): void
    {
        $request = $this->request();

        $this->actingAs($request->resident->user, 'homeowner')
            ->get(route('portal.requests', ['document' => RequestType::CERTIFICATION]))
            ->assertOk()
            ->assertSee('data-modal-auto-open', false);

        $this->actingAs($request->resident->user, 'homeowner')
            ->get(route('portal.requests'))
            ->assertOk()
            ->assertDontSee('data-modal-auto-open', false);
    }

    public function test_an_approved_request_keeps_view_but_loses_edit_and_delete(): void
    {
        $request = $this->request(RequestStatus::APPROVED);

        $this->actingAs($request->resident->user, 'homeowner')
            ->get(route('portal.requests'))
            ->assertOk()
            ->assertSee('data-target="#requestViewModal"', false)
            ->assertDontSee('data-target="#requestEditModal"', false)
            ->assertDontSee('data-target="#requestWithdrawModal"', false);
    }

    public function test_an_own_pending_request_can_be_edited(): void
    {
        $request = $this->request();

        Livewire::actingAs($request->resident->user, 'homeowner')
            ->test(RequestEditModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', true)
            ->set('form.purpose', 'others')
            ->set('form.reason', 'A corrected reason')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('request-edited');

        $this->assertSame('others', $request->fresh()->purpose);
        $this->assertSame('A corrected reason', $request->fresh()->reason);
    }

    public function test_an_approved_request_cannot_be_edited(): void
    {
        $request = $this->request(RequestStatus::APPROVED);
        $owner = $request->resident->user;

        $this->assertFalse($owner->can('updateOwn', $request));

        Livewire::actingAs($owner, 'homeowner')
            ->test(RequestEditModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', false);

        $this->expectException(ValidationException::class);

        app(UpdateRequest::class)->handle($request, [
            'type'   => RequestType::CERTIFICATION,
            'reason' => 'A rewrite after approval',
        ], null);
    }

    public function test_another_homeowners_request_cannot_be_read_or_edited(): void
    {
        $request = $this->request();
        $other = factory(User::class)->create();

        $this->assertFalse($other->can('viewOwn', $request));
        $this->assertFalse($other->can('updateOwn', $request));

        Livewire::actingAs($other, 'homeowner')
            ->test(RequestViewModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', false);

        Livewire::actingAs($other, 'homeowner')
            ->test(RequestEditModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', false);
    }

    public function test_the_view_modal_shows_the_request_and_its_claiming_schedule(): void
    {
        $claimingAt = now()->addDays(2)->setTime(9, 30);
        $request = $this->request(RequestStatus::APPROVED, null, $claimingAt);
        $schedule = $request->fresh()->claiming_at;

        Livewire::actingAs($request->resident->user, 'homeowner')
            ->test(RequestViewModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', true)
            ->assertSee($request->reference)
            ->assertSee($request->type_label)
            ->assertSee('Claiming Date')
            ->assertSee($schedule->format('F j, Y'))
            ->assertSee('Claiming Time')
            ->assertSee($schedule->format('g:i A'));
    }

    public function test_a_pending_request_has_no_claiming_schedule_to_show(): void
    {
        $request = $this->request();

        Livewire::actingAs($request->resident->user, 'homeowner')
            ->test(RequestViewModal::class)
            ->call('load', $request->id)
            ->assertSet('loaded', true)
            ->assertDontSee('Claiming Date')
            ->assertDontSee('Claiming Time');
    }

    public function test_the_approve_modal_defaults_to_now_and_saves_the_chosen_schedule(): void
    {
        $this->signInAdmin();

        $request = $this->request();
        $claimingAt = now()->addDay()->setTime(14, 0);

        Livewire::test(RequestApproveModal::class)
            ->call('load', $request->id)
            ->assertSet('claimingAt', now()->format('Y-m-d\TH:i'))
            ->set('claimingAt', $claimingAt->format('Y-m-d\TH:i'))
            ->call('approve')
            ->assertHasNoErrors()
            ->assertEmitted('request-saved');

        $request = $request->fresh();

        $this->assertTrue($request->status->isApproved());
        $this->assertNotNull($request->claiming_at);
        $this->assertSame(
            $claimingAt->format('Y-m-d H:i'),
            $request->claiming_at->format('Y-m-d H:i')
        );

        Livewire::actingAs($request->resident->user, 'homeowner')
            ->test(RequestViewModal::class)
            ->call('load', $request->id)
            ->assertSee($request->claiming_date_label)
            ->assertSee($request->claiming_time_label);
    }

    public function test_the_approve_modal_requires_a_future_claiming_schedule(): void
    {
        $this->signInAdmin();

        $request = $this->request();

        Livewire::test(RequestApproveModal::class)
            ->call('load', $request->id)
            ->set('claimingAt', '')
            ->call('approve')
            ->assertHasErrors(['claimingAt' => 'required']);

        Livewire::test(RequestApproveModal::class)
            ->call('load', $request->id)
            ->set('claimingAt', now()->subDay()->format('Y-m-d\TH:i'))
            ->call('approve')
            ->assertHasErrors(['claimingAt' => 'after_or_equal']);

        $this->assertSame(RequestStatus::PENDING, $request->fresh()->status->value);
    }

    private function request(
        string $status = RequestStatus::PENDING,
        ?User $owner = null,
        ?Carbon $claimingAt = null
    ): RequestModel {
        $owner = $owner ?: factory(User::class)->create();

        return RequestModel::create([
            'resident_id' => $owner->resident_id,
            'type'        => RequestType::CERTIFICATION,
            'purpose'     => 'residency',
            'reason'      => 'For a scholarship application',
            'status'      => $status,
            'claiming_at' => $claimingAt,
        ]);
    }

    private function signInAdmin(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'      => 'Officer',
            'email'     => 'request-management-' . uniqid() . '@example.test',
            'password'  => Hash::make('Secret123!'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
