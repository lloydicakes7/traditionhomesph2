<?php

namespace App\Http\Livewire\Admin\Homeowners\Property\Modals;

use App\Actions\Homeowners\Properties\DeleteProperties;
use App\Http\Livewire\AdminModalComponent;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Models\Property;

/**
 * Delete Selected Properties — the confirmation the list's mass-delete bar opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class PropertyMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'propertyMassDeleteModal';

    public $selectedIds = [];

    protected $listeners = [
        'loadPropertyMassDeleteModal'  => 'load',
        'closePropertyMassDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var DeleteProperties */
    protected $deleteProperties;

    public function boot(DeleteProperties $deleteProperties)
    {
        $this->deleteProperties = $deleteProperties;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAuthorizable(): string
    {
        return Property::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'properties';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteProperties->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'property-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.property.modals.property-mass-delete-modal');
    }
}
