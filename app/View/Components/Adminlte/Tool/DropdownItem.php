<?php

namespace App\View\Components\Adminlte\Tool;

use Illuminate\View\Component;
use JeroenNoten\LaravelAdminLte\Helpers\UtilsHelper;

class DropdownItem extends Component
{
    /**
     * The item text/label.
     *
     * @var string
     */
    public $text;

    /**
     * A Font Awesome icon for the item.
     *
     * @var string
     */
    public $icon;

    /**
     * An URL for the item.
     *
     * @var string
     */
    public $url;

    /**
     * Indicates if a divider should be rendered before this item.
     *
     * @var bool|mixed
     */
    public $divider;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $text = null, $icon = null, $url = null, $divider = null
    ) {
        $this->text = UtilsHelper::applyHtmlEntityDecoder($text);
        $this->icon = $icon;
        $this->url = $url;
        $this->divider = $divider;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.tool.dropdown-item');
    }
}
