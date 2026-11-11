<?php

namespace App\Queries\Homeowners\Users;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserLinkQuery
{
    // ===== Candidates =====

    public function unlinkedResidents(): Collection
    {
        return Resident::whereDoesntHave('user')->orderBy('surname')->orderBy('first_name')->get();
    }

    public function unlinkedUsers(): Collection
    {
        return User::whereNull('resident_id')->orderBy('email')->get(['id', 'email']);
    }
}
