<?php

namespace App\Http\Livewire\Admin\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Resident;
use App\Models\User;
use App\Queries\Homeowners\Users\UserLinkQuery;
use Illuminate\Database\Eloquent\Collection;

/**
 * The shared "link a resident and a user account" dialog.
 *
 * It runs one way or the other — linking a resident to an account
 * (`to-user`), or an account to a resident (`to-resident`) — and which
 * candidates it offers follows from that. The direction is fixed per host page
 * (passed in as the `mode` property); the source record comes from the row's
 * trigger (`data-toggle` + `data-target` + `data-id`), which
 * public/js/modal-loader.js relays as `loadLinkResidentUserModal`. Confirming
 * emits `link-resident-user` for the page to act on.
 */
class LinkResidentUserModal extends AdminModalComponent
{
    /** @var string 'to-user' (link a resident to an account) or 'to-resident' (the reverse); fixed by the host page */
    public $mode = 'to-user';

    /** @var int|null Source record: a user id for 'to-user', a resident id for 'to-resident' */
    public $sourceId = null;

    /** @var string Counterpart picked in the dialog */
    public $selectedId = '';

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadLinkResidentUserModal'  => 'load',
        'openLinkResidentUserModal'  => 'refreshModal',
        'closeLinkResidentUserModal' => 'closeModal',
    ];

    /** @var UserLinkQuery */
    protected $query;

    public function boot(UserLinkQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'selectedId' => ['required', $this->mode === 'to-user'
                ? 'exists:residents,id'
                : 'exists:users,id'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    /**
     * Show the dialog for the source record the trigger named.
     *
     * @param  int|string  $id  The user or resident passed as the trigger's data-id
     */
    public function load($id): void
    {
        $id = (int) $id;

        if (! $id || ! in_array($this->mode, ['to-user', 'to-resident'], true)) {
            return;
        }

        $this->authorize('update', $this->mode === 'to-user' ? User::class : Resident::class);

        $this->sourceId   = $id;
        $this->selectedId = '';

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the state when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        // mode is the host page's configuration, not per-open state, so it
        // survives a close.
        $this->sourceId   = null;
        $this->selectedId = '';

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Confirm
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded || ! $this->mode || ! $this->sourceId) {
            return;
        }

        $this->validate();

        $this->emit('link-resident-user', $this->sourceId, (int) $this->selectedId, $this->mode);

        $this->dispatchBrowserEvent('modal-close', ['id' => 'linkResidentUserModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.modals.link-resident-user-modal', [
            'candidates' => $this->loaded ? $this->candidates() : collect(),
        ]);
    }

    /**
     * The records the dialog offers as the other half of the link.
     */
    private function candidates(): Collection
    {
        return $this->mode === 'to-user'
            ? $this->query->unlinkedResidents()
            : $this->query->unlinkedUsers();
    }
}
