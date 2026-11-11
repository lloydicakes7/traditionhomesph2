<?php

namespace App\View\Components\Adminlte\Tool;

use Illuminate\View\Component;

class Tabs extends Component
{
    /**
     * Array of tabs with id, title, icon, active keys.
     *
     * @var array
     */
    public $tabs;

    /**
     * The tabs type (tabs or pills).
     *
     * @var string
     */
    public $type;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($tabs = [], $type = 'tabs')
    {
        $this->tabs = $tabs;
        $this->type = $type;
    }

    /**
     * Make the nav class.
     *
     * @return string
     */
    public function makeNavClass()
    {
        $classes = ['nav'];

        if ($this->type === 'pills') {
            $classes[] = 'nav-pills';
        } else {
            $classes[] = 'nav-tabs';
        }

        return implode(' ', $classes);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.tool.tabs');
    }
}
