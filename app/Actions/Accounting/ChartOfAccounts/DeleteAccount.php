<?php

namespace App\Actions\Accounting\ChartOfAccounts;

use App\Models\Account\Account;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class DeleteAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Account $account, Admin $by): void
    {
        if ($blocker = $account->deletionBlocker()) {
            throw ValidationException::withMessages(['account' => $blocker]);
        }

        $label = $account->label;

        $account->delete();

        $this->activity->log($by, 'deleted', 'Chart of Accounts', 'Removed account ' . $label);
    }
}
