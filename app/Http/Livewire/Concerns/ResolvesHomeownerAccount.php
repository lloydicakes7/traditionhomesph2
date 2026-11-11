<?php

namespace App\Http\Livewire\Concerns;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * The account a portal component is working for.
 *
 * The portal runs on its own guard: `homeowner`. Every portal component resolves
 * the signed-in account through this trait rather than through the default
 * guard, which belongs to the admin panel — the two panels can be signed in at
 * the same time, and they are different people.
 */
trait ResolvesHomeownerAccount
{
    /**
     * Make the portal's guard the default one, on every Livewire request.
     *
     * A policy check resolves its user from the default guard, and the portal's
     * own pages never set it for the actions that follow them: Livewire sends
     * those to POST /livewire/message/..., which passes through the `web`
     * middleware group and not through the `homeowner` route group. Left alone,
     * the admin panel's guard would answer for a homeowner and every policy
     * check on this panel would be denied. booted() runs on the first request and
     * on every one after it, before the component does anything.
     *
     * @return void
     */
    public function booted(): void
    {
        Auth::shouldUse('homeowner');
    }

    /**
     * The signed-in homeowner's account.
     *
     * @return \App\Models\User
     */
    protected function account(): User
    {
        return auth('homeowner')->user();
    }

    /**
     * The resident the account was issued for.
     *
     * A portal account implies a profiled resident — the office profiles the
     * person first and issues the account for them — but the profile is read
     * defensively: officers may still be filling it in.
     *
     * @return \App\Models\Resident|null
     */
    protected function resident(): ?Resident
    {
        return $this->account()->resident;
    }
}
