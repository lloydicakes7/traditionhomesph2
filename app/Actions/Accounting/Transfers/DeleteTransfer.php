<?php

namespace App\Actions\Accounting\Transfers;

use App\Models\Account\AccountTransfer;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class DeleteTransfer
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(AccountTransfer $transfer, Admin $by): void
    {
        $label = $transfer->reference;

        DB::transaction(function () use ($transfer, $by) {
            JournalEntry::voidFor($transfer);

            $transfer->delete();
        });

        $this->activity->log($by, 'deleted', 'Transfers', 'Removed transfer ' . $label);
    }
}
