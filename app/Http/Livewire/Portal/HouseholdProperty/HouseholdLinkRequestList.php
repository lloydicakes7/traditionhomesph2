<?php

namespace App\Http\Livewire\Portal\HouseholdProperty;

use App\Actions\Homeowners\Households\ApproveHouseholdLink;
use App\Actions\Homeowners\Households\RejectHouseholdLink;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Models\Household\Household;
use App\Models\Household\HouseholdLinkRequest;
use App\Queries\Homeowners\Households\HouseholdLinkRequestListQuery;

class HouseholdLinkRequestList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    public function mount(): void
    {
        $this->household();
    }

    public function approve(int $linkId): void
    {
        $link = $this->authorizedLink($linkId);

        try {
            app(ApproveHouseholdLink::class)->handle($link, $this->account());
        } catch (\RuntimeException $exception) {
            $this->emit('notify', 'warning', __('households.link.failed'));

            return;
        }

        $this->emit('notify', 'success', __('households.link.approved'));
    }

    public function reject(int $linkId): void
    {
        $link = $this->authorizedLink($linkId);

        try {
            app(RejectHouseholdLink::class)->handle($link, $this->account());
        } catch (\RuntimeException $exception) {
            $this->emit('notify', 'warning', __('households.link.failed'));

            return;
        }

        $this->emit('notify', 'info', __('households.link.rejected'));
    }

    private function household(): Household
    {
        $household = $this->account()->household;

        abort_if($household === null, 403);

        $this->authorize('updateOwn', $household);

        return $household;
    }

    private function authorizedLink(int $linkId): HouseholdLinkRequest
    {
        $household = $this->household();

        $link = HouseholdLinkRequest::query()
            ->where('household_id', $household->id)
            ->findOrFail($linkId);

        $this->authorize('decideOwn', $link);

        return $link;
    }

    public function render(HouseholdLinkRequestListQuery $query)
    {
        $household = $this->household();

        return view('livewire.portal.household-property.household-link-request-list', [
            'household' => $household->load('property.address'),
            'requests'  => $query->pendingFor($household->id),
        ]);
    }
}
