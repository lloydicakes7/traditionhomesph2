<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Http\Livewire\Concerns\WithFieldLabels;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

abstract class ModalComponent extends Component
{
    use AuthorizesRequests;
    use WithFieldLabels;

    protected $paginationTheme = 'bootstrap';

    public bool $loaded = false;

    public function closeModal(): void
    {
        $this->resetModal();
    }

    /**
     * Re-render once the shell has shown.
     *
     * Bootstrap's `show.bs.modal` pulls the skeleton back over the content so a
     * `data-toggle`-opened dialog can load lazily; modal-loader relays that as
     * `open{Stem}Modal`. A dialog the server opened itself is already loaded, so
     * this hook simply re-renders it and the loaded content takes its place
     * again.
     */
    public function refreshModal(): void
    {
        $this->markLoaded();
    }

    protected function markLoaded(): void
    {
        $this->loaded = true;
    }

    protected function resetModal(): void
    {
        $this->loaded = false;
    }
}
