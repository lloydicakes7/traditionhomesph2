<?php

namespace App\Actions\Collections\PaymentMethods;

use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class DeletePaymentMethod
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(PaymentMethod $method, Admin $by): bool
    {
        $deleted = DB::transaction(function () use ($method) {
            if ($method->payments()->exists()) {
                return false;
            }

            StorageHelper::deletePhoto($method->qr_path);

            $method->delete();

            return true;
        });

        if (! $deleted) {
            return false;
        }

        $this->activity->log(
            $by,
            'deleted',
            'payment_method',
            "Deleted payment method \"{$method->name}\"",
            ['payment_method_id' => $method->id]
        );

        return true;
    }
}
