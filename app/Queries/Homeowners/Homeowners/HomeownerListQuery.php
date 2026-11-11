<?php

namespace App\Queries\Homeowners\Homeowners;

use App\Enums\BillStatus;
use App\Enums\RegistrationStatus;
use App\Enums\RequestStatus;
use App\Enums\ViolationStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HomeownerListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(
        string $search = '',
        string $sortField = 'surname',
        string $sortDirection = 'asc',
        string $accountStatus = '',
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        return $this->filtered($search, $accountStatus)
            ->select('users.*')
            ->selectSub(function ($q) {
                $q->from('household_members')
                    ->whereColumn('household_members.resident_id', 'users.resident_id')
                    ->selectRaw('COUNT(*)');
            }, 'member_count')
            ->selectSub(function ($q) {
                $q->from('requests')
                    ->whereColumn('requests.resident_id', 'users.resident_id')
                    ->where('requests.status', RequestStatus::PENDING)
                    ->selectRaw('COUNT(*)');
            }, 'pending_request_count')
            ->selectSub(function ($q) {
                $q->from('violations')
                    ->whereColumn('violations.resident_id', 'users.resident_id')
                    ->where('violations.status', ViolationStatus::ACTIVE)
                    ->selectRaw('COUNT(*)');
            }, 'active_violation_count')
            ->selectSub(function ($q) {
                $q->from('bills')
                    ->whereColumn('bills.user_id', 'users.id')
                    ->whereIn('bills.status', [
                        BillStatus::UNPAID,
                        BillStatus::PARTIAL,
                        BillStatus::OVERDUE,
                    ])
                    ->selectRaw('COUNT(*)');
            }, 'unpaid_bill_count')
            ->orderBy($sortField === 'email' ? 'email' : 'users.id', $sortDirection)
            ->paginate($perPage);
    }

    public function ids(string $search = '', string $accountStatus = ''): array
    {
        return $this->filtered($search, $accountStatus)
            ->pluck('id')
            ->map(function ($id) {
                return (string) $id;
            })
            ->all();
    }

    // ===== Helpers =====

    private function filtered(string $search = '', string $accountStatus = '')
    {
        return User::query()
            ->where('registration_status', RegistrationStatus::APPROVED)
            ->when($accountStatus === 'active', function ($q) {
                $q->where('is_active', true);
            })
            ->when($accountStatus === 'inactive', function ($q) {
                $q->where('is_active', false);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($wq) use ($search) {
                    $wq->where('email', 'like', "%{$search}%")
                        ->orWhereHas('resident', function ($rq) use ($search) {
                            $rq->where('surname', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('mobile_no', 'like', "%{$search}%");
                        });
                });
            });
    }
}
