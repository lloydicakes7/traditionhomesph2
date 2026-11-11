<?php

namespace App\Http\Livewire\Admin\Homeowners\Address;

use App\Actions\Homeowners\Addresses\ToggleAddressPrimary;
use App\Http\Livewire\AdminComponent;
use App\Models\Address;
use App\Queries\Homeowners\Addresses\AddressListQuery;
use Livewire\WithPagination;

class AddressTableList extends AdminComponent
{
    use WithPagination;

    protected $listeners = [
        'addressSaved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AddressListQuery */
    protected $query;

    /** @var ToggleAddressPrimary */
    protected $togglePrimary;

    public function boot(AddressListQuery $query, ToggleAddressPrimary $togglePrimary)
    {
        $this->query = $query;
        $this->togglePrimary = $togglePrimary;
    }

    // =====================================================================
    // Toggle
    // =====================================================================

    public function togglePrimary($id): void
    {
        $this->authorize('update', Address::class);

        $nowPrimary = $this->togglePrimary->handle((int) $id, auth('web')->user());

        $this->emit('notify', 'success', $nowPrimary ? 'Primary address updated.' : 'Primary address removed.');
        $this->emit('addressSaved');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Address::class);
        $addresses = $this->query->paginate();

        return view('livewire.admin.homeowners.address.address-table-list', [
            'addresses' => $addresses,
        ]);
    }
}
