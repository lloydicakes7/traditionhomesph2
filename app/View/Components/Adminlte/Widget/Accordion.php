<?php

namespace App\View\Components\Adminlte\Widget;

use Illuminate\View\Component;

class Accordion extends Component
{
    /**
     * The accordion ID (used for collapse targeting).
     *
     * @var string
     */
    public $id;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id = 'accordion')
    {
        $this->id = $id;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.widget.accordion');
    }
}
