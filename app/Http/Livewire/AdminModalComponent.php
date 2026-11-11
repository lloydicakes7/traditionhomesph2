<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Concerns\ForcesGuard;
use App\Livewire\ModalComponent;

abstract class AdminModalComponent extends ModalComponent
{
    use ForcesGuard;

    public function hydrate(): void
    {
        $this->useGuard('web');
    }
}
