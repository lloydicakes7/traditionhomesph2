<?php

namespace App\Http\Livewire\Admin\Support\Complaints\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Complaint;
use App\Queries\Support\Complaints\ComplaintDetailQuery;

class ComplaintViewModal extends AdminModalComponent
{
    public $viewingComplaintId = null;

    public $complaint = null;

    protected $listeners = [
        'loadComplaintViewModal'  => 'load',
        'closeComplaintViewModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var ComplaintDetailQuery */
    protected $detail;

    public function boot(ComplaintDetailQuery $detail)
    {
        $this->detail = $detail;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('view', Complaint::class);

        $this->complaint = $this->detail->find($id);
        $this->viewingComplaintId = $this->complaint->id;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->viewingComplaintId = null;
        $this->complaint = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.complaints.modals.complaint-view-modal');
    }
}
