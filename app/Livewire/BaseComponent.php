<?php

namespace App\Livewire;

use App\Http\Livewire\Concerns\WithFieldLabels;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class BaseComponent extends Component
{
    use authorizesRequests;
    use WithFieldLabels;

    protected $paginationTheme = 'bootstrap';
}
