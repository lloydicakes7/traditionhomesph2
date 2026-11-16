<?php

namespace App\Queries\Homeowners\Profiles;

use App\Enums\ProfileStatus;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The profiles the office has to work, in the order it works them: what is
 * waiting first, then what was sent back. A verified profile has left the queue.
 */
class ProfileVerificationListQuery
{
    // ===== Queries =====

    /**
     * @return LengthAwarePaginator
     */
    public function paginate(string $search = '', int $perPage = 15)
    {
        return User::query()
            ->select([
                'id', 'resident_id', 'email', 'profile_status',
                'profile_submitted_at', 'profile_verified_at', 'created_at',
            ])
            ->with([
                'resident:id,surname,first_name,middle_name,mobile_no,valid_id_path',
                'resident.household.property',
            ])
            ->whereIn('profile_status', [
                ProfileStatus::SUBMITTED,
                ProfileStatus::REJECTED,
            ])
            ->when(trim($search) !== '', function ($query) use ($search) {
                $term = '%' . trim($search) . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('email', 'like', $term)
                        ->orWhereHas('resident', function ($resident) use ($term) {
                            $resident->where('surname', 'like', $term)
                                ->orWhere('first_name', 'like', $term)
                                ->orWhere('middle_name', 'like', $term);
                        });
                });
            })
            ->orderByRaw("case profile_status when 'submitted' then 0 else 1 end")
            ->orderByDesc('profile_submitted_at')
            ->paginate($perPage);
    }
}
