<?php

namespace App\Queries\Homeowners\Homeowners;

use App\Models\User;

class HomeownerStatsQuery
{
    // ===== Summary =====

    public function summary(): array
    {
        $total = User::query()->approvedRegistration()->count();
        $active = User::query()->approvedRegistration()->where('is_active', true)->count();
        $disabled = $total - $active;

        $activePct = $total > 0 ? (int) floor($active / $total * 100) : 0;

        if ($total > 0) {
            $activeDeg = $activePct * 3.6;
            $donutStyle = "conic-gradient(#28a745 0deg {$activeDeg}deg, #ffc107 {$activeDeg}deg 360deg)";
        } else {
            $donutStyle = 'conic-gradient(#e9ecef 0% 100%)';
        }

        return [
            'total'      => $total,
            'active'     => $active,
            'disabled'   => $disabled,
            'activePct'  => $activePct,
            'donutStyle' => $donutStyle,
        ];
    }
}
