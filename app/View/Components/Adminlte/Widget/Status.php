<?php

namespace App\View\Components\Adminlte\Widget;

use Illuminate\View\Component;

class Status extends Component
{
    /**
     * The status text/label.
     *
     * @var string
     */
    public $text;

    /**
     * The status theme (success, danger, warning, info, primary, secondary).
     *
     * @var string
     */
    public $theme;

    /**
     * Indicates if a colored dot should be shown.
     *
     * @var bool|mixed
     */
    public $dot;

    /**
     * A Font Awesome icon replacing the dot.
     *
     * @var string
     */
    public $icon;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $text = null, $theme = 'primary', $dot = null, $icon = null
    ) {
        $this->text = $text;
        $this->theme = $theme;
        $this->dot = $dot ?? true; // Show dot by default
        $this->icon = $icon;
    }

    /**
     * Make the class for the dot indicator.
     *
     * @return string
     */
    public function makeDotClass()
    {
        $classes = ['status-dot', "bg-{$this->theme}"];

        return implode(' ', $classes);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.widget.status');
    }
}
