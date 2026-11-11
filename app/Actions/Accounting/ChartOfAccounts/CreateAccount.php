<?php

namespace App\Actions\Accounting\ChartOfAccounts;

use App\Enums\AccountKind;
use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Account
    {
        $kind = AccountKind::from($data['kind']);
        $starting = bcadd((string) ($data['starting_balance'] ?? '0'), '0', 2);

        if (bccomp($starting, '0', 2) < 0) {
            throw ValidationException::withMessages([
                'starting_balance' => 'A starting balance cannot be below zero.',
            ]);
        }

        $account = DB::transaction(function () use ($kind, $starting, $data, $by) {
            $account = Account::create([
                'code'        => trim((string) $data['code']),
                'name'        => trim((string) $data['name']),
                'type'        => $kind->type(),
                'description' => filled($data['description'] ?? null) ? $data['description'] : null,
                'is_cash'     => $kind->isCash(),
            ]);

            if ($kind->isCash() && bccomp($starting, '0', 2) > 0) {
                JournalEntry::post([
                    'entry_date'     => $data['balance_as_of'] ?? now(),
                    'classification' => JournalClassification::STANDARD,
                    'source_type'    => JournalSource::MANUAL,
                    'source_id'      => $account->id,
                    'memo'           => 'Starting balance — ' . $account->name,
                    'lines'          => [
                        [
                            'account_id' => $account->id,
                            'side'       => JournalLine::SIDE_DEBIT,
                            'amount'     => $starting,
                            'memo'       => 'Starting balance',
                        ],
                        [
                            'account_name' => JournalEntry::EXTERNAL_ACCOUNT_NAME,
                            'side'         => JournalLine::SIDE_CREDIT,
                            'amount'       => $starting,
                            'memo'         => 'Starting balance',
                        ],
                    ],
                ], $by->id);
            }

            return $account;
        });

        $this->activity->log(
            $by,
            'created',
            'Chart of Accounts',
            "Added account {$account->code} — {$account->name}",
            ['account_id' => $account->id]
        );

        return $account;
    }
}
