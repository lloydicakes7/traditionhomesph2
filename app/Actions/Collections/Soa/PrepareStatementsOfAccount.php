<?php

namespace App\Actions\Collections\Soa;

use App\Enums\SoaStatus;
use App\Models\Admin;
use App\Models\SoaEmail;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Utility\PeriodHelper;
use Illuminate\Support\Facades\DB;

class PrepareStatementsOfAccount
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(string $period, Admin $by): int
    {
        $prepared = DB::transaction(function () use ($period, $by) {
            return $this->prepare($period, $by);
        });

        $this->activity->log(
            $by,
            'prepared',
            'soa_email',
            'Prepared ' . $prepared . ' statement(s) of account for ' . PeriodHelper::label($period),
            ['period' => $period, 'prepared' => $prepared]
        );

        return $prepared;
    }

    // ===== Helpers =====

    private function prepare(string $period, Admin $by): int
    {
        $prepared = 0;

        foreach ($this->recipients($period) as $user) {
            if ($this->hasStatement($user, $period)) {
                continue;
            }

            SoaEmail::create([
                'user_id'          => $user->id,
                'email'            => $user->email,
                'period'           => $period,
                'status'           => SoaStatus::PENDING,
                'sent_by_admin_id' => $by->id,
            ]);

            $prepared++;
        }

        return $prepared;
    }

    private function recipients(string $period)
    {
        return User::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereHas('bills', function ($query) use ($period) {
                $query->open()->forMonth($period);
            })
            ->orderBy('id')
            ->get();
    }

    private function hasStatement(User $user, string $period): bool
    {
        return SoaEmail::query()
            ->where('user_id', $user->id)
            ->where('period', $period)
            ->exists();
    }
}
