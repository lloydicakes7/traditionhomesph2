<?php

namespace App\Queries\Portal;

use App\Enums\ComplaintStatus;
use App\Enums\RequestStatus;
use App\Enums\ViolationStatus;
use App\Models\Amenity\AmenityBooking;
use App\Models\Complaint;
use App\Models\Payment;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Models\Violation;
use App\Utility\PeriodHelper;
use Illuminate\Database\Eloquent\Collection;

class DashboardQuery
{
    const RECENT_LIMIT = 5;

    // ===== Queries =====

    public function overview(User $account): array
    {
        $residentId = $account->resident_id;
        $year = (int) now()->format('Y');

        $stats = $this->statsFor($residentId);

        return [
            'stats' => $stats,
            'charts' => [
                'year'     => $year,
                'statuses' => $this->statusRows($residentId),
                'bookings' => $this->bookingRows($residentId, $year),
                'payments' => $this->paymentRows((int) $account->id, $year),
            ],
            'openRequests'  => $this->recentRequests($residentId),
            'totalRequests' => $stats['requests'] ?? 0,
        ];
    }

    public function recentRequests(?int $residentId): Collection
    {
        if ($residentId === null) {
            return new Collection();
        }

        return RequestModel::query()
            ->forResident($residentId)
            ->with(['processedByAdmin', 'bill'])
            ->ordered()
            ->limit(self::RECENT_LIMIT)
            ->get();
    }

    // ===== Helpers =====

    private function statsFor(?int $residentId): array
    {
        if ($residentId === null) {
            return ['requests' => null, 'complaints' => null, 'violations' => null, 'bookings' => null];
        }

        return [
            'requests'   => RequestModel::query()->forResident($residentId)->count(),
            'complaints' => Complaint::query()->forResident($residentId)->count(),
            'violations' => Violation::query()->forResident($residentId)->count(),
            'bookings'   => AmenityBooking::query()->forResident($residentId)->count(),
        ];
    }

    private function statusRows(?int $residentId): array
    {
        if ($residentId === null) {
            return [];
        }

        $rows = [];

        foreach ($this->requestCounts($residentId) as $status => $total) {
            $rows[] = $this->statusRow('Requests', RequestStatus::labels()[$status] ?? $status, $total);
        }

        foreach ($this->complaintCounts($residentId) as $status => $total) {
            $rows[] = $this->statusRow('Complaints', ComplaintStatus::labels()[$status] ?? $status, $total);
        }

        foreach ($this->violationCounts($residentId) as $status => $total) {
            if ($total > 0) {
                $rows[] = $this->statusRow('Violations', ViolationStatus::labels()[$status] ?? $status, $total);
            }
        }

        return $rows;
    }

    private function requestCounts(int $residentId): array
    {
        return RequestModel::query()
            ->forResident($residentId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    private function complaintCounts(int $residentId): array
    {
        return Complaint::query()
            ->forResident($residentId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();
    }

    private function violationCounts(int $residentId): array
    {
        return [
            ViolationStatus::ACTIVE   => Violation::query()->forResident($residentId)->active()->count(),
            ViolationStatus::RESOLVED => Violation::query()->forResident($residentId)->resolved()->count(),
        ];
    }

    private function statusRow(string $group, string $label, $total): array
    {
        return [
            'label' => $group . ' · ' . $label,
            'value' => (int) $total,
        ];
    }

    private function bookingRows(?int $residentId, int $year): array
    {
        if ($residentId === null) {
            return [];
        }

        $counts = array_fill(1, 12, 0);

        $starts = AmenityBooking::query()
            ->forResident($residentId)
            ->whereYear('starts_at', $year)
            ->pluck('starts_at');

        foreach ($starts as $startsAt) {
            $counts[(int) $startsAt->format('n')]++;
        }

        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $rows[] = $this->chartRow($year, $month, (float) $counts[$month]);
        }

        return $rows;
    }

    private function paymentRows(int $userId, int $year): array
    {
        $totals = array_fill(1, 12, 0.0);

        $payments = Payment::query()
            ->forUser($userId)
            ->whereYear('paid_at', $year)
            ->get(['amount', 'paid_at']);

        foreach ($payments as $payment) {
            $month = (int) $payment->paid_at->format('n');
            $totals[$month] += (float) $payment->amount;
        }

        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $rows[] = $this->chartRow($year, $month, round($totals[$month], 2));
        }

        return $rows;
    }

    private function chartRow(int $year, int $month, float $value): array
    {
        $period = $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        return [
            'label' => PeriodHelper::shortLabel($period),
            'value' => $value,
        ];
    }
}
