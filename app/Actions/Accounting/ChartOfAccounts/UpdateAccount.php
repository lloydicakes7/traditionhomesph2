<?php

namespace App\Actions\Accounting\ChartOfAccounts;

use App\Enums\AccountKind;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class UpdateAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Account $account, array $data, Admin $by): Account
    {
        if ($account->isSystem()) {
            throw ValidationException::withMessages([
                'account' => 'System accounts are kept by the books and cannot be changed here.',
            ]);
        }

        if ($managedBy = $account->managedByLabel()) {
            throw ValidationException::withMessages([
                'account' => 'This account is managed from ' . $managedBy . ' — rename it there.',
            ]);
        }

        $kind = AccountKind::from($data['kind']);
        $currentKind = $account->kind();

        $payload = [
            'code'        => trim((string) $data['code']),
            'name'        => trim((string) $data['name']),
            'description' => filled($data['description'] ?? null) ? $data['description'] : null,
        ];

        if (! $kind->is($currentKind)) {
            if ($account->isDefaultCash()) {
                throw ValidationException::withMessages([
                    'kind' => 'Cash on Hand is the default cash account, so its kind is fixed.',
                ]);
            }

            if ($account->journalLines()->exists()) {
                throw ValidationException::withMessages([
                    'kind' => 'This account already has journal entries, so its kind is fixed.',
                ]);
            }

            $payload['type'] = $kind->type();
            $payload['is_cash'] = $kind->isCash();
        }

        $account->update($payload);

        $this->activity->log(
            $by,
            'updated',
            'Chart of Accounts',
            "Updated account {$account->code} — {$account->name}",
            ['account_id' => $account->id]
        );

        return $account;
    }
}
