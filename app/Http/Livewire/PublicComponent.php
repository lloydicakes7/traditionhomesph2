<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Concerns\ForcesGuard;
use App\Livewire\BaseComponent;

abstract class PublicComponent extends BaseComponent
{
    use ForcesGuard;

    public function hydrate(): void
    {
        $this->useGuard('web');
    }
}
