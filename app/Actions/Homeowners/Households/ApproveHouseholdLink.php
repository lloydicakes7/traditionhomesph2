<?php

namespace App\Actions\Homeowners\Households;

use App\Enums\HouseholdLinkStatus;
use App\Models\Household\HouseholdLinkRequest;
use App\Models\Household\HouseholdMember;
use App\Models\User;
use App\Notifications\HouseholdLinkDecided;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveHouseholdLink
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

        DB::transaction(function () use ($link) {
            if (HouseholdMember::residentIsPlaced($link->resident_id)) {
                throw new \RuntimeException('This resident already belongs to a household.');
            }

            HouseholdMember::create([
                'household_id' => $link->household_id,
                'resident_id'  => $link->resident_id,
                'relationship' => $link->relationship,
                'is_head'      => false,
            ]);

            $link->forceFill([
                'status'     => HouseholdLinkStatus::APPROVED,
                'decided_at' => now(),
            ])->save();
        });

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
