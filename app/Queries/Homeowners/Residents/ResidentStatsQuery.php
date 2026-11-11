<?php

namespace App\Queries\Homeowners\Residents;

use App\Models\Resident;

class ResidentStatsQuery
{
    // ===== Summary =====

    public function summary(): array
    {
        $total = Resident::count();
        $withAccount = Resident::has('user')->count();

        return [
            'total'          => $total,
            'withAccount'    => $withAccount,
            'withoutAccount' => $total - $withAccount,
            'active'         => Resident::whereHas('user', function ($q) {
                $q->where('is_active', true);
            })->count(),
            'disabled'       => Resident::whereHas('user', function ($q) {
                $q->where('is_active', false);
            })->count(),
        ];
    }
}
