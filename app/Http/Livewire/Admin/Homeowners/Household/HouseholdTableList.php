<?php

namespace App\Http\Livewire\Admin\Homeowners\Household;

use App\Http\Livewire\AdminComponent;
use App\Http\Livewire\Concerns\WithSorting;
use App\Models\Household\Household;
use App\Queries\Homeowners\Households\HouseholdListQuery;
use Livewire\WithPagination;

/**
 * Households page — the list surface: search, sort, pagination.
 *
 * The form/view/delete dialogs open by native Bootstrap JS straight from their
 * row buttons; the edit-members dialog is the shared Admin\Modals\EditMembersModal,
 * opened the same way. Saving that dialog emits `edit-members-saved`, so the
 * list refreshes for the members the household just gained or lost.
 *
 * Split (one folder per page):
 *   HouseholdTableList — this component
 *   HouseholdStats     — the 4 stat cards (listens household-saved)
 *   HouseholdCsv       — import/export toolbar (uses WithCsvImport)
 *   Modals\*           — every modal as its own ModalComponent
 */
class HouseholdTableList extends AdminComponent
{
    use WithPagination,
        WithSorting;

    // =====================================================================
    // List state
    // =====================================================================

    public $search = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    protected $queryString = ['search', 'sortField', 'sortDirection'];

    protected $listeners = [
        'household-saved'    => '$refresh',
        'edit-members-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HouseholdListQuery */
    protected $query;

    public function boot(HouseholdListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Household::class);

        $households = $this->query->paginate(
            $this->search,
            $this->sortField,
            $this->sortDirection
        );

        return view('livewire.admin.homeowners.household.household-table-list', [
            'households' => $households,
        ]);
    }
}
