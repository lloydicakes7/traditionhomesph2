<?php

namespace App\Actions\Collections\AdvanceDeposits;

use App\Enums\AdvanceDepositStatus;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class RecordAdvanceDeposit
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): AdvanceDeposit
    {
        $deposit = DB::transaction(function () use ($data) {
            $user = User::findOrFail($data['user_id']);
            $amount = bcadd((string) $data['amount'], '0', 2);

            return AdvanceDeposit::create([
                'user_id'      => $user->id,
                'amount'       => $amount,
                'balance'      => $amount,
                'status'       => AdvanceDepositStatus::AVAILABLE,
                'deposited_at' => $data['deposited_at'],
                'notes'        => ($data['notes'] ?? null) ?: null,
            ]);
        });

        $this->activity->log(
            $by,
            'created',
            'advance_deposit',
            "Recorded an advance deposit of {$deposit->amount_display} for {$deposit->holder_name}",
            ['advance_deposit_id' => $deposit->id, 'user_id' => $deposit->user_id]
        );

        return $deposit;
    }
}
