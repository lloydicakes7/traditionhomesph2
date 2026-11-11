<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner;

use App\Actions\Homeowners\Users\AcceptRegistration;
use App\Http\Livewire\AdminComponent;
use App\Models\User;
use App\Queries\Homeowners\Homeowners\PendingRegistrationListQuery;
use Livewire\WithPagination;

/**
 * The accounts filed through the public registration wizard, waiting for the
 * office to accept them.
 */
class PendingRegistrationTableList extends AdminComponent
{
    use WithPagination;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'homeowner-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var PendingRegistrationListQuery */
    protected $query;

    /** @var AcceptRegistration */
    protected $acceptRegistration;

    public function boot(PendingRegistrationListQuery $query, AcceptRegistration $acceptRegistration)
    {
        $this->query = $query;
        $this->acceptRegistration = $acceptRegistration;
    }

    // =====================================================================
    // Lifecycle
    // =====================================================================

    /**
     * A new search starts the list over.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Actions
    // =====================================================================

    /**
     * Accept one registration, so the applicant may sign in.
     */
    public function accept($id): void
    {
        $this->authorize('update', User::class);

        $user = $this->acceptRegistration->handle((int) $id, auth('web')->user());

        $this->emit('notify', 'success', "Registration of {$user->name} accepted.");
        $this->emit('homeowner-saved');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.homeowner.pending-registration-table-list', [
            'registrations' => $this->query->paginate($this->search),
        ]);
    }
}
