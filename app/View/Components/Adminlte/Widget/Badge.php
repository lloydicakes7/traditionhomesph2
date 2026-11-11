<?php

namespace App\View\Components\Adminlte\Widget;

use Illuminate\View\Component;

class Badge extends Component
{
    /**
     * The badge text/content.
     *
     * @var string
     */
    public $text;

    /**
     * The badge theme (primary, secondary, success, danger, warning, info,
     * dark, light).
     *
     * @var string
     */
    public $theme;

    /**
     * A Font Awesome icon for the badge.
     *
     * @var string
     */
    public $icon;

    /**
     * Indicates if the badge is a pill (rounded) variant.
     *
     * @var bool|mixed
     */
    public $pill;

    /**
     * The badge size (sm or lg).
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
        $text = null, $theme = 'primary', $icon = null, $pill = null,
        $size = null
    ) {
        $this->text = $text;
        $this->theme = $theme;
        $this->icon = $icon;
        $this->pill = $pill;
        $this->size = $size;
    }

    /**
     * Make the class attribute for the badge.
     *
     * @return string
     */
    public function makeBadgeClass()
    {
        $classes = ['badge', "badge-{$this->theme}"];

        if (isset($this->pill)) {
            $classes[] = 'badge-pill';
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
        return view('adminlte::components.widget.badge');
    }
}
