<?php

namespace App\Actions\Collections\PaymentMethods;

use App\Actions\Collections\PaymentMethods\Concerns\PaymentMethodPayload;
use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class CreatePaymentMethod
{
    use PaymentMethodPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, $qr, Admin $by): PaymentMethod
    {
        $method = DB::transaction(function () use ($data, $qr) {
            $payload = $this->payload($data);

            $payload['sort_order'] = (int) PaymentMethod::query()->max('sort_order') + 1;

            if ($qr) {
                $payload['qr_path'] = StorageHelper::storePhoto($qr, PaymentMethod::QR_DIRECTORY);
            }

            return PaymentMethod::create($payload);
        });

        $this->activity->log(
            $by,
            'created',
            'payment_method',
            "Added payment method \"{$method->name}\"",
            ['payment_method_id' => $method->id]
        );

        return $method;
    }
}
