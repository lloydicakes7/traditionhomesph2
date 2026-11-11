<?php

namespace App\View\Components\Adminlte\Widget;

use Illuminate\View\Component;
use JeroenNoten\LaravelAdminLte\Helpers\UtilsHelper;

class AccordionItem extends Component
{
    /**
     * The accordion parent ID (used for collapse data-parent).
     *
     * @var string
     */
    public $accordionId;

    /**
     * The item title shown in the header.
     *
     * @var string
     */
    public $title;

    /**
     * Indicates if the item is initially shown (expanded).
     *
     * @var bool|mixed
     */
    public $show;

    /**
     * A Font Awesome icon for the header.
     *
     * @var string
     */
    public $icon;

    /**
     * The item theme for the card header (primary, secondary, info, etc.).
     *
     * @var string
     */
    public $theme;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $title = null, $accordionId = null, $show = null,
        $icon = null, $theme = null
    ) {
        $this->title = UtilsHelper::applyHtmlEntityDecoder($title);
        $this->accordionId = $accordionId;
        $this->show = $show;
        $this->icon = $icon;
        $this->theme = $theme;
    }

    /**
     * Get a unique ID for this collapse item.
     *
     * @return string
     */
    public function collapseId()
    {
        return "collapse-{$this->accordionId}-" . md5($this->title ?? uniqid());
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.widget.accordion-item');
    }
}
