<?php

namespace App\Actions\HoaCenter\Faqs;

use App\Models\Admin;
use App\Models\Faq;
use App\Services\ActivityLogService;

class UpdateFaq
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Faq $faq, array $data, Admin $by): Faq
    {
        $faq->update(Faq::payloadFrom($data));

        $this->activity->log(
            $by,
            'updated',
            'faq',
            "Updated the FAQ \"{$faq->question}\"",
            ['faq_id' => $faq->id]
        );

        return $faq;
    }
}
