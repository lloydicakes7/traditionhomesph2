<?php

namespace App\View\Components\Form;

use JeroenNoten\LaravelAdminLte\View\Components\Form\Input;

class MoneyInput extends Input
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.form.money-input');
    }
}
