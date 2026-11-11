<?php

namespace App\Http\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait ForcesGuard
{
    protected function useGuard(string $guard): void
    {
        Auth::shouldUse($guard);
    }
}
