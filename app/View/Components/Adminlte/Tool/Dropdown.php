<?php

namespace App\View\Components\Adminlte\Tool;

use Illuminate\View\Component;

class Dropdown extends Component
{
    /**
     * The dropdown trigger button text.
     *
     * @var string
     */
    public $text;

    /**
     * The dropdown theme for the trigger button.
     *
     * @var string
     */
    public $theme;

    /**
     * A Font Awesome icon for the trigger button.
     *
     * @var string
     */
    public $icon;

    /**
     * The dropdown direction (dropup, dropright, dropleft).
     *
     * @var string
     */
    public $direction;

    /**
     * The button size (sm, lg).
     *
     * @var string
     */
    public $size;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $text = 'Actions', $theme = 'primary', $icon = null,
        $direction = null, $size = null
    ) {
        $this->text = $text;
        $this->theme = $theme;
        $this->icon = $icon;
        $this->direction = $direction;
        $this->size = $size;
    }

    /**
     * Make the class attribute for the dropdown wrapper.
     *
     * @return string
     */
    public function makeWrapperClass()
    {
        $classes = ['dropdown'];

        if (isset($this->direction)) {
            $classes[] = $this->direction;
        }

        return implode(' ', $classes);
    }

    /**
     * Make the class attribute for the trigger button.
     *
     * @return string
     */
    public function makeButtonClass()
    {
        $classes = ['btn', "btn-{$this->theme}", 'dropdown-toggle'];

        if (isset($this->size)) {
            $classes[] = "btn-{$this->size}";
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
        return view('adminlte::components.tool.dropdown');
    }
}
