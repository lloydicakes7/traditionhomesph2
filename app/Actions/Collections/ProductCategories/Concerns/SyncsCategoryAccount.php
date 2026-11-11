<?php

namespace App\Actions\Collections\ProductCategories\Concerns;

use App\Enums\AccountType;
use App\Models\Account\Account;
use App\Models\Product\ProductCategory;
use App\Services\ActivityLogService;

trait SyncsCategoryAccount
{
    // ===== Payload =====

    private function payload(array $data): array
    {
        return [
            'name'        => $data['name'],
            'description' => ($data['description'] ?? null) ?: null,
        ];
    }

    // ===== Account =====

    /**
     * The income account this category's sales post to.
     *
     * A category is also a line of the chart, so the account it earns into is
     * created with it and renamed with it.
     */
    private function syncIncomeAccount(
        ProductCategory $category,
        ActivityLogService $activity,
        int $adminId
    ): void {
        $existing = $category->account_id ? Account::find($category->account_id) : null;
        $renamed = $existing && $existing->name !== trim((string) $category->name);

        $account = Account::syncCategory((string) $category->name, AccountType::INCOME(), $existing);

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
