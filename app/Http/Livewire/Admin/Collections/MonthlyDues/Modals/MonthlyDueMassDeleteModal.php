<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues\Modals;

use App\Actions\Collections\MonthlyDues\DeleteMonthlyDues;
use App\Http\Livewire\Concerns\WithMassDelete;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;

/**
 * Delete Selected Monthly Dues — the confirmation the list's mass-delete bar
 * opens.
 *
 * The list's mass-delete bar is a plain Bootstrap trigger: it carries the
 * selected ids as the loader's single `data-id` (public/js/modal-loader.js), so
 * the dialog opens at the click, shows its skeleton, and loads the selection
 * without a round trip to the list.
 */
class MonthlyDueMassDeleteModal extends AdminModalComponent
{
    use WithMassDelete;

    const MODAL_ID = 'dueMassDeleteModal';

    /** @var array Due ids handed over by the list */
    public $selectedIds = [];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadDueMassDeleteModal'  => 'load',
        'closeDueMassDeleteModal' => 'closeModal',
    ];

    /** @var DeleteMonthlyDues */
    protected $deleteDues;

    public function boot(DeleteMonthlyDues $deleteDues)
    {
        $this->deleteDues = $deleteDues;
    }

    protected function resetModal(): void
    {
        $this->selectedIds = [];

        parent::resetModal();
    }

    // =====================================================================
    // WithMassDelete
    // =====================================================================

    protected function massDeleteAbility(): string
    {
        return 'deleteMonthlyDue';
    }

    protected function massDeleteAuthorizable(): string
    {
        return Bill::class;
    }

    protected function massDeleteLabel(): string
    {
        return 'monthly due invoices';
    }

    protected function massDeleteIds(array $ids): int
    {
        return $this->deleteDues->handle($ids, auth('web')->user());
    }

    protected function massDeleteSavedEvent(): ?string
    {
        return 'due-saved';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.monthly-dues.modals.monthly-due-mass-delete-modal');
    }
}
