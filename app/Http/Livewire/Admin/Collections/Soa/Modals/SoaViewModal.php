<?php

namespace App\Http\Livewire\Admin\Collections\Soa\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\SoaEmail;
use App\Queries\Collections\Soa\StatementDataQuery;
use App\Queries\Collections\Soa\StatementListQuery;

/**
 * View Statement — the account as it will be mailed: what was billed in the
 * period, what is outstanding, and the association's own payment instructions.
 *
 * Opened client-side by Bootstrap (`#soaViewModal`) with the statement's id on
 * the trigger, and loaded lazily by public/js/modal-loader.js. The preview and
 * the mailed copy both come from the same summary
 * (\App\Queries\Collections\Soa\StatementDataQuery::summary()), so an officer previews
 * exactly what the homeowner will receive.
 */
class SoaViewModal extends AdminModalComponent
{
    /** @var int|null Statement being shown */
    public $soaId = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadSoaViewModal'  => 'load',
        'closeSoaViewModal' => 'closeModal',
    ];

    /** @var StatementListQuery */
    protected $query;

    /** @var StatementDataQuery */
    protected $data;

    public function boot(StatementListQuery $query, StatementDataQuery $data)
    {
        $this->query = $query;
        $this->data = $data;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when this runs
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', SoaEmail::class);

        $this->soaId = $id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->soaId = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $statement = $this->soaId ? $this->query->find($this->soaId) : null;

        return view('livewire.admin.collections.soa.modals.soa-view-modal', [
            'statement' => $statement,
            'summary'   => $statement ? $this->data->summary($statement) : null,
        ]);
    }
}
