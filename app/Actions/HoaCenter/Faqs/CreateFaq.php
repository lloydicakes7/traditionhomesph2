<?php

namespace App\Actions\HoaCenter\Faqs;

use App\Models\Admin;
use App\Models\Faq;
use App\Services\ActivityLogService;

class CreateFaq
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Faq
    {
        $faq = Faq::create(Faq::payloadFrom($data));

        $this->activity->log(
            $by,
            'created',
            'faq',
            "Added the FAQ \"{$faq->question}\"",
            ['faq_id' => $faq->id]
        );

        return $faq;
    }
}
