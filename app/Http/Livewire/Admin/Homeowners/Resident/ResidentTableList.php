<?php

namespace App\Http\Livewire\Admin\Homeowners\Resident;

use App\Actions\Homeowners\Users\UnlinkResidentAccount;
use App\Http\Livewire\AdminComponent;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\Concerns\HandlesResidentUserLink;
use App\Models\Resident;
use App\Queries\Homeowners\Residents\ResidentListQuery;
use Livewire\WithPagination;

/**
 * Residents page — the list surface: search, sort, pagination and selection.
 *
 * The form/view/delete dialogs open by native Bootstrap JS straight from their
 * row buttons (`data-toggle` + `data-id`) — the mass-delete bar carries its
 * selection to its own dialog the same way; this component carries only the
 * actions that genuinely need the server, the account link/unlink.
 *
 * Split (one folder per page):
 *   ResidentTableList  — this component
 *   ResidentStats      — the 4 stat cards (listens resident-saved)
 *   ResidentCsv        — import/export toolbar (uses WithCsvImport)
 *   Modals\*           — every modal as its own ModalComponent
 */
class ResidentTableList extends AdminComponent
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

    /** @var array Selected resident IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'resident-saved'     => 'refreshList',
        'link-resident-user' => 'handleResidentUserLink',
    ];

    /** @var ResidentListQuery */
    protected $query;

    /** @var UnlinkResidentAccount */
    protected $unlinkAccount;

    public function boot(ResidentListQuery $query, UnlinkResidentAccount $unlinkAccount)
    {
        $this->query = $query;
        $this->unlinkAccount = $unlinkAccount;
    }

    // =====================================================================
    // HandlesResidentUserLink
    // =====================================================================

    protected function linkSavedEvent(): string
    {
        return 'resident-saved';
    }

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids($this->search);
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
     * Another component wrote a resident — including the mass-delete dialog.
     * Drop the selection it consumed, so a bulk delete never leaves the toolbar
     * standing over rows that are already gone.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * Unlink the portal account from a resident.
     */
    public function unlinkAccount($id): void
    {
        $this->authorize('update', Resident::class);

        if ($this->unlinkAccount->handle((int) $id, auth('web')->user())) {
            $this->emit('notify', 'success', 'User account unlinked successfully.');
        } else {
            $this->emit('notify', 'warning', 'This resident has no linked account to remove.');
        }
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $residents = $this->query->paginate(
            $this->search,
            $this->sortField,
            $this->sortDirection
        );

        return view('livewire.admin.homeowners.resident.resident-table-list', [
            'residents' => $residents,
        ]);
    }
}
