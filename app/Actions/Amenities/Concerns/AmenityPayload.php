<?php

namespace App\Actions\Amenities\Concerns;

use App\Enums\Weekday;
use App\Models\Amenity\Amenity;

trait AmenityPayload
{
    // ===== Payload =====

    private function payload(array $data): array
    {
        return [
            'name'        => $data['name'],
            'rate_type'   => $data['rate_type'] ?? null,
            'rate'        => (float) ($data['rate'] ?? 0),
            'is_bookable' => (bool) ($data['is_bookable'] ?? false),
            'description' => ($data['description'] ?? null) ?: null,
        ];
    }

    // ===== Schedule =====

    private function saveWeek(Amenity $amenity, array $week): void
    {
        $amenity->schedules()->delete();

        foreach (Weekday::cases() as $day) {
            $opens = $week[$day->value]['opens_at'] ?? null;
            $closes = $week[$day->value]['closes_at'] ?? null;

            $isClosed = $opens === null || $opens === '' || $closes === null || $closes === '';

            $amenity->schedules()->create([
                'day_of_week' => $day->value,
                'opens_at'    => $isClosed ? null : $opens,
                'closes_at'   => $isClosed ? null : $closes,
            ]);
        }

        $amenity->unsetRelation('schedules');
    }
}
