<?php

namespace App\Actions\Homeowners\Households;

use App\Enums\HouseholdLinkStatus;
use App\Models\Household\HouseholdLinkRequest;
use App\Models\User;
use App\Notifications\HouseholdLinkDecided;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class RejectHouseholdLink
{
    private $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function handle(HouseholdLinkRequest $link, User $by): HouseholdLinkRequest
    {
        if (! $link->isPending()) {
            throw new \RuntimeException('This household request is no longer waiting for an answer.');
        }

        $link->forceFill([
            'status'     => HouseholdLinkStatus::REJECTED,
            'decided_at' => now(),
        ])->save();

        $this->tellTheOfficers($link, $by);

        return $link;
    }

    private function tellTheOfficers(HouseholdLinkRequest $link, User $by): void
    {
        try {
            $this->notifications->toOfficers(new HouseholdLinkDecided($link, $by));
        } catch (\Throwable $exception) {
            Log::error('Household link decision notification failed: ' . get_class($exception));
        }
    }
}
