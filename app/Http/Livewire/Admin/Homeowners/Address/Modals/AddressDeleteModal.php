<?php

namespace App\Http\Livewire\Admin\Homeowners\Address\Modals;

use App\Actions\Homeowners\Addresses\DeleteAddress;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Address;
use App\Queries\Homeowners\Addresses\AddressListQuery;

class AddressDeleteModal extends AdminModalComponent
{
    public $deletingAddressId = null;

    public $deletingAddressData = null;

    protected $listeners = [
        'loadAddressDeleteModal'  => 'load',
        'closeAddressDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AddressListQuery */
    protected $query;

    /** @var DeleteAddress */
    protected $deleteAddress;

    public function boot(AddressListQuery $query, DeleteAddress $deleteAddress)
    {
        $this->query = $query;
        $this->deleteAddress = $deleteAddress;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Address::class);

        $address = $this->query->find((int) $id);

        $this->deletingAddressId = $address->id;
        $this->deletingAddressData = [
            'id'    => $address->id,
            'label' => $address->label,
            'full'  => $address->full_address,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingAddressId = null;
        $this->deletingAddressData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingAddressId) {
            return;
        }

        $this->authorize('delete', Address::class);
        $this->deleteAddress->handle($this->deletingAddressId, auth('web')->user());

        $this->emit('notify', 'success', 'Address deleted successfully.');
        $this->emit('addressSaved');
        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => 'addressDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.address.modals.address-delete-modal');
    }
}
