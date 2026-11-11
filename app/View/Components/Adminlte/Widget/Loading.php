<?php

namespace App\View\Components\Adminlte\Widget;

use Illuminate\View\Component;

class Loading extends Component
{
    /**
     * Number of skeleton lines to show.
     *
     * @var int
     */
    public $lines;

    /**
     * The loading type (skeleton or spinner).
     *
     * @var string
     */
    public $type;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($lines = 3, $type = 'skeleton')
    {
        $this->lines = max(1, (int) $lines);
        $this->type = $type;
    }

    /**
     * Generate an array of widths for each skeleton line.
     *
     * @return array
     */
    public function lineWidths()
    {
        $widths = [];

        for ($i = 0; $i < $this->lines - 1; $i++) {
            $widths[] = rand(75, 100);
        }

        // Last line is always shorter for visual effect.
        $widths[] = rand(40, 70);

        return $widths;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.widget.loading');
    }
}
