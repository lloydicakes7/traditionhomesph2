<?php

namespace App\Queries\Homeowners\Homeowners;

use App\Enums\RegistrationStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The accounts filed through the public registration wizard that the office has
 * not accepted yet, newest first.
 */
class PendingRegistrationListQuery
{
    const PER_PAGE = 15;

    // ===== Rows =====

    public function paginate(
        string $search = '',
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        return $this->filtered($search)
            ->select('users.*')
            ->with('resident')
            ->orderByDesc('users.created_at')
            ->orderByDesc('users.id')
            ->paginate($perPage);
    }

    /**
     * One registration, for the detail dialog. Only a pending registration is
     * read: an accepted account is a homeowner and lives on the list instead.
     */
    public function find(int $id): User
    {
        return User::query()
            ->pendingRegistration()
            ->with(['resident', 'household.property.address'])
            ->findOrFail($id);
    }

    public function count(): int
    {
        return User::query()
            ->where('registration_status', RegistrationStatus::PENDING)
            ->count();
    }

    // ===== Helpers =====

    private function filtered(string $search = '')
    {
        return User::query()
            ->where('registration_status', RegistrationStatus::PENDING)
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
