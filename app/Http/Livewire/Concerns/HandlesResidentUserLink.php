<?php

namespace App\Http\Livewire\Concerns;

use App\Actions\Homeowners\Users\LinkUserAccount;

trait HandlesResidentUserLink
{
    // =====================================================================
    // Listeners
    // =====================================================================

    public function handleResidentUserLink($sourceId, $selectedId, string $mode): void
    {
        $this->authorize('update', $mode === 'to-user' ? \App\Models\User::class : \App\Models\Resident::class);

        $userId     = $mode === 'to-user' ? (int) $sourceId : (int) $selectedId;
        $residentId = $mode === 'to-user' ? (int) $selectedId : (int) $sourceId;

        if (! app(LinkUserAccount::class)->handle($userId, $residentId, auth('web')->user())) {
            $this->emit('notify', 'error', 'Link failed — one of the accounts is already linked to another record.');
            return;
        }

        $this->emit('notify', 'success', 'User account linked successfully.');

        $this->emit($this->linkSavedEvent());
    }

    // =====================================================================
    // Abstracts
    // =====================================================================

    abstract protected function linkSavedEvent(): string;
}
