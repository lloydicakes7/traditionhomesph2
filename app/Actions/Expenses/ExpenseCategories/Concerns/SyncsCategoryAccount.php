<?php

namespace App\Actions\Expenses\ExpenseCategories\Concerns;

use App\Enums\AccountType;
use App\Models\Account\Account;
use App\Models\Expense\ExpenseCategory;
use App\Services\ActivityLogService;

trait SyncsCategoryAccount
{
    // ===== Account =====

    /**
     * The expense account this category's spending is charged to.
     *
     * A category is also a line of the chart, so the account it is charged to
     * is created with it and renamed with it.
     */
    private function syncAccount(
        ExpenseCategory $category,
        ActivityLogService $activity,
        int $adminId
    ): void {
        $existing = $category->account_id ? Account::find($category->account_id) : null;
        $renamed = $existing && $existing->name !== trim((string) $category->name);

        $account = Account::syncCategory((string) $category->name, AccountType::EXPENSE(), $existing);

        if ((int) $category->account_id !== (int) $account->id) {
            $category->update(['account_id' => $account->id]);
        }

        if (! $existing) {
            $activity->log(
                $adminId,
                'created',
                'Chart of Accounts',
                "Added account {$account->code} — {$account->name}",
                ['account_id' => $account->id]
            );
        }

        if ($renamed) {
            $activity->log(
                $adminId,
                'updated',
                'Chart of Accounts',
                "Updated account {$account->code} — {$account->name}",
                ['account_id' => $account->id]
            );
        }
    }
}
