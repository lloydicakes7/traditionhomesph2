<?php

namespace App\Actions\HoaCenter\Faqs;

use App\Models\Admin;
use App\Models\Faq;
use App\Services\ActivityLogService;

class DeleteFaq
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(Faq $faq, Admin $by): void
    {
        $question = $faq->question;

        $faq->delete();

        $this->activity->log(
            $by,
            'deleted',
            'faq',
            "Removed the FAQ \"{$question}\"",
            ['faq_id' => $faq->id]
        );
    }
}
