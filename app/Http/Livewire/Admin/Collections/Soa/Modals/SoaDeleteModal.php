<?php

namespace App\Http\Livewire\Admin\Collections\Soa\Modals;

use App\Actions\Collections\Soa\DeleteStatementsOfAccount;
use App\Http\Livewire\AdminModalComponent;
use App\Models\SoaEmail;
use App\Queries\Collections\Soa\StatementListQuery;

/**
 * Remove Statement — the confirmation a row's Delete action opens.
 *
 * Opened client-side by Bootstrap (`#soaDeleteModal`) with the statement's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js.
 */
class SoaDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'soaDeleteModal';

    /** @var int|null Statement the confirmation applies to */
    public $deletingSoaId = null;

    /** @var string Period and property the confirmation names */
    public $deletingSoaLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadSoaDeleteModal'  => 'load',
        'closeSoaDeleteModal' => 'closeModal',
    ];

    /** @var StatementListQuery */
    protected $query;

    /** @var DeleteStatementsOfAccount */
    protected $deleteStatements;

    public function boot(StatementListQuery $query, DeleteStatementsOfAccount $deleteStatements)
    {
        $this->query = $query;
        $this->deleteStatements = $deleteStatements;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', SoaEmail::class);

        $statement = $this->query->find($id);

        $this->deletingSoaId = $statement->id;
        $this->deletingSoaLabel = $statement->period_display . ' · ' . $statement->property_label;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingSoaId = null;
        $this->deletingSoaLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingSoaId) {
            return;
        }

        $this->authorize('delete', SoaEmail::class);

        $this->deleteStatements->handle([$this->deletingSoaId], auth('web')->user());

        $this->emit('notify', 'success', 'Statement removed.');
        $this->emit('soa-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.collections.soa.modals.soa-delete-modal');
    }
}
