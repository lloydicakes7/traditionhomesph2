<?php

namespace App\Actions\Collections\PaymentMethods;

use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class TogglePaymentMethodActive
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    /**
     * Switch a method on or off. Payments already recorded keep the method
     * they were taken with.
     */
    public function handle(PaymentMethod $method, Admin $by): bool
    {
        $active = DB::transaction(function () use ($method) {
            $method->update(['is_active' => ! $method->is_active]);

            return (bool) $method->is_active;
        });

        $this->activity->log(
            $by,
            'toggled',
            'payment_method',
            $active
                ? "Turned on payment method \"{$method->name}\""
                : "Turned off payment method \"{$method->name}\"",
            ['payment_method_id' => $method->id]
        );

        return $active;
    }
}
