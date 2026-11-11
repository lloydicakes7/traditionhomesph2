<?php

namespace App\Actions\Collections\PaymentMethods;

use App\Actions\Collections\PaymentMethods\Concerns\PaymentMethodPayload;
use App\Models\Admin;
use App\Models\PaymentMethod;
use App\Services\ActivityLogService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;

class UpdatePaymentMethod
{
    use PaymentMethodPayload;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(PaymentMethod $method, array $data, $qr, bool $removeQr, Admin $by): PaymentMethod
    {
        $method = DB::transaction(function () use ($method, $data, $qr, $removeQr) {
            $payload = $this->payload($data);

            if ($qr) {
                $payload['qr_path'] = StorageHelper::replacePhoto(
                    $qr,
                    $method->qr_path,
                    PaymentMethod::QR_DIRECTORY
                );
            } elseif ($removeQr) {
                StorageHelper::deletePhoto($method->qr_path);
                $payload['qr_path'] = null;
            }

            $method->update($payload);

            return $method;
        });

        $this->activity->log(
            $by,
            'updated',
            'payment_method',
            "Updated payment method \"{$method->name}\"",
            ['payment_method_id' => $method->id]
        );

        return $method;
    }
}
