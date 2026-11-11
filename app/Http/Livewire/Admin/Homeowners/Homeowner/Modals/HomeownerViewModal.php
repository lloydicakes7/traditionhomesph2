<?php

namespace App\Http\Livewire\Admin\Homeowners\Homeowner\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Resident;
use App\Models\Violation;
use App\Queries\Homeowners\Homeowners\HomeownerDetailQuery;
use App\Queries\Support\Violations\PortalViolationQuery;

class HomeownerViewModal extends AdminModalComponent
{
    const MODAL_ID = 'homeownerViewModal';

    /** @var \App\Models\User|null User model loaded for view modal */
    public $viewHomeowner = null;

    /** @var array|null Household data loaded for the Household tab */
    public $viewHouseholdData = null;

    /** @var int|null The account behind the resident, for the money tabs */
    public $viewingUserId = null;

    /** @var string Active tab in the view modal */
    public $activeViewTab = 'details';

    protected $listeners = [
        'loadHomeownerViewModal'  => 'load',
        'closeHomeownerViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var HomeownerDetailQuery */
    protected $query;

    /** @var PortalViolationQuery */
    protected $violations;

    public function boot(HomeownerDetailQuery $query, PortalViolationQuery $violations)
    {
        $this->query = $query;
        $this->violations = $violations;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load($id): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('viewAny', User::class);

        $viewData = $this->query->view((int) $id);

        $this->viewHomeowner     = $viewData['resident'];
        $this->viewHouseholdData = $viewData['household'];
        $this->viewingUserId     = $viewData['resident']->user?->id;
        $this->activeViewTab     = 'details';

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewHomeowner     = null;
        $this->viewHouseholdData = null;
        $this->viewingUserId     = null;
        $this->activeViewTab     = 'details';

        parent::resetModal();
    }

    // =====================================================================
    // Tabs
    // =====================================================================

    /**
     * Switch between the tabs of the dialog.
     */
    public function switchViewTab($tab): void
    {
        $this->activeViewTab = $tab;
    }

    // =====================================================================
    // The member's violation record
    // =====================================================================

    /**
     * The member's violations, for an officer entitled to read them.
         *
     * @param  \App\Models\Resident|null  $user
     * @return array{active: \Illuminate\Support\Collection, history: \Illuminate\Support\Collection}|null
     */
    private function violationsFor(?Resident $resident): ?array
    {
        if (! $resident || ! auth('web')->user()->can('viewAny', Violation::class)) {
            return null;
        }

        return $this->violations->forResident($resident->id);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $admin = auth('web')->user();
        $residentId = $this->viewHomeowner?->id;

        return view('livewire.admin.homeowners.homeowner.modals.homeowner-view-modal', [
            'canViewViolations' => $admin->can('viewAny', Violation::class),
            'viewViolations'    => $this->violationsFor($this->viewHomeowner),
            'canViewRequests'   => $admin->can('viewAny', RequestModel::class),
            'canViewComplaints' => $admin->can('viewAny', Complaint::class),
            'canViewInvoices'   => $admin->can('viewAnyInvoices', Bill::class),
            'canViewPayments'   => $admin->can('viewAny', Payment::class),
            'viewRequests'      => $residentId ? $this->query->requests($residentId) : collect(),
            'viewComplaints'    => $residentId ? $this->query->complaints($residentId) : collect(),
            'viewInvoices'      => $this->query->invoices($this->viewingUserId),
            'viewPayments'      => $this->query->payments($this->viewingUserId),
        ]);
    }
}
