<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner;

use App\Actions\Homeowners\Users\ToggleUserAccount;
use App\Http\Livewire\AdminComponent;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\Concerns\HandlesResidentUserLink;
use App\Models\User;
use App\Queries\Homeowners\Homeowners\HomeownerListQuery;
use Livewire\WithPagination;

class HomeownerTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        HandlesResidentUserLink;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Sort field */
    public $sortField = 'surname';

    /** @var string Sort direction */
    public $sortDirection = 'asc';

    /** @var string Account status filter: '', 'active' or 'inactive' */
    public $accountStatus = '';

    /** @var array Selected account IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages (the table owns the selection) */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'sortField', 'sortDirection', 'accountStatus'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'homeowner-saved'    => 'refreshList',
        'edit-members-saved' => '$refresh',
        'link-resident-user' => 'handleResidentUserLink',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HomeownerListQuery */
    protected $query;

    /** @var ToggleUserAccount */
    protected $toggleAccount;

    public function boot(HomeownerListQuery $query, ToggleUserAccount $toggleAccount)
    {
        $this->query = $query;
        $this->toggleAccount = $toggleAccount;
    }

    // =====================================================================
    // HandlesResidentUserLink
    // =====================================================================

    protected function linkSavedEvent(): string
    {
        return 'homeowner-saved';
    }

    // =====================================================================
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids($this->search, $this->accountStatus);
    }

    /**
     * A changed filter starts the list over: the rows it counted are not the
     * rows it shows.
     */
    public function updatedAccountStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle "all pages" using the service-filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    // =====================================================================
    // Row actions needing the server
    // =====================================================================

    /**
     * Another component wrote a homeowner — including the mass-delete dialog.
     * Drop the selection it consumed, so a bulk delete never leaves the toolbar
     * standing over rows that are already gone.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * Toggle the active status of a homeowner's account.
     */
    public function toggleAccountStatus($id): void
    {
        $this->authorize('toggleActive', User::class);

        $newStatus = $this->toggleAccount->handle((int) $id, auth('web')->user());

        $status = $newStatus ? 'enabled' : 'disabled';
        $this->emit('notify', 'success', "Account {$status} successfully.");
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $users = $this->query->paginate(
            $this->search,
            $this->sortField,
            $this->sortDirection,
            $this->accountStatus
        );

        return view('livewire.admin.homeowners.homeowner.homeowner-table-list', [
            'users' => $users,
        ]);
    }
}
