<?php

namespace App\Actions\Collections\MonthlyDues;

use App\Models\Account\AccountingPeriod;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\Collections\MonthlyDueBillingService;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordAdvanceMonthlyDues
{
    private MonthlyDueBillingService $billing;

    private ActivityLogService $activity;

    public function __construct(MonthlyDueBillingService $billing, ActivityLogService $activity)
    {
        $this->billing = $billing;
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): array
    {
        $user = User::with('household.property')->findOrFail($data['user_id']);
        $property = $user->household?->property;

        if (! $property) {
            throw ValidationException::withMessages([
                'user_id' => 'That homeowner has no household unit on file, so monthly dues cannot be billed.',
            ]);
        }

        $months = PeriodHelper::between($data['from'], $data['to']);
        $deposit = $this->depositFor($data, $user);

        $result = DB::transaction(function () use ($months, $user, $property, $deposit, $data, $by) {
            $this->guardClosedPeriods($months);

            $result = ['created' => 0, 'settled' => 0, 'skipped' => 0, 'total' => '0.00'];

            foreach ($months as $month) {
                $this->payMonth($month, $user, $property, $deposit, $data, $by, $result);
            }

            return $result;
        });

        if ($result['settled'] > 0) {
            $result['total_display'] = MoneyHelper::format($result['total']);

            $this->activity->log(
                $by,
                'received',
                'monthly_due',
                'Recorded ' . $result['settled'] . ' advance monthly due payment(s) for ' . $user->name
                    . ' from ' . PeriodHelper::label($data['from']) . ' to ' . PeriodHelper::label($data['to'])
                    . ($result['skipped'] ? ' (' . $result['skipped'] . ' month(s) skipped)' : ''),
                [
                    'user_id' => $user->id,
                    'from'    => $data['from'],
                    'to'      => $data['to'],
                    'created' => $result['created'],
                    'settled' => $result['settled'],
                    'skipped' => $result['skipped'],
                    'total'   => $result['total'],
                ]
            );
        }

        return $result;
    }

    // ===== Helpers =====

    private function payMonth(
        string $month,
        User $user,
        Property $property,
        ?AdvanceDeposit $deposit,
        array $data,
        Admin $by,
        array &$result
    ): void {
        $due = Bill::query()
            ->monthlyDues()
            ->forUser($user->id)
            ->where('billing_period', $month)
            ->lockForUpdate()
            ->first();

        if (! $due) {
            $due = $this->billing->raise(
                $property,
                $user,
                $month,
                $this->billing->dueDateFor($month),
                $this->billing->billingMethod(),
                $by->id
            );

            $result['created']++;
        }

        if ($this->cannotCollect($due)) {
            $result['skipped']++;

            return;
        }

        $amount = (string) $due->balance;

        if ($deposit && bccomp($amount, (string) $deposit->balance, 2) > 0) {
            throw ValidationException::withMessages([
                'advance_deposit_id' => 'The selected advance deposit does not hold enough credit for every month in this batch.',
            ]);
        }

        $due->settle($this->paymentData($amount, $deposit, $data, $by));

        if ($deposit) {
            $deposit->applyCredit((float) $amount);
        }

        $result['settled']++;
        $result['total'] = bcadd($result['total'], $amount, 2);
    }

    private function cannotCollect(Bill $due): bool
    {
        if (! $due->status_enum || ! $due->status_enum->isCollectible()) {
            return true;
        }

        if (bccomp((string) $due->balance, '0.01', 2) < 0) {
            return true;
        }

        return Payment::query()->waitingForBill($due->id)->exists();
    }

    private function paymentData(string $amount, ?AdvanceDeposit $deposit, array $data, Admin $by): array
    {
        return [
            'amount'               => $amount,
            'advance_deposit_id'   => $deposit?->id,
            'payment_method_id'    => $deposit ? null : (($data['payment_method_id'] ?? null) ?: null),
            'reference_no'         => $deposit ? 'AD-' . $deposit->id : (($data['reference_no'] ?? null) ?: null),
            'paid_at'              => filled($data['paid_at'] ?? null) ? $data['paid_at'] : now(),
            'notes'                => ($data['notes'] ?? null) ?: null,
            'received_by_admin_id' => $by->id,
        ];
    }

    private function depositFor(array $data, User $user): ?AdvanceDeposit
    {
        $id = (int) ($data['advance_deposit_id'] ?? 0);

        if ($id === 0) {
            return null;
        }

        $deposit = AdvanceDeposit::find($id);

        if (! $deposit || (int) $deposit->user_id !== (int) $user->id || ! $deposit->is_open) {
            throw ValidationException::withMessages([
                'advance_deposit_id' => 'That advance deposit is not available for this homeowner.',
            ]);
        }

        return $deposit;
    }

    private function guardClosedPeriods(array $months): void
    {
        foreach ($months as $month) {
            if (AccountingPeriod::locksPeriod($month)) {
                throw ValidationException::withMessages([
                    'from' => PeriodHelper::label($month) . ' is closed. Reopen the period before billing it.',
                ]);
            }
        }
    }
}
