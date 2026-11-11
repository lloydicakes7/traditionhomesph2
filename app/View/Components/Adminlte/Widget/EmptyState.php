<?php

namespace App\View\Components\Adminlte\Widget;

use Illuminate\View\Component;
use JeroenNoten\LaravelAdminLte\Helpers\UtilsHelper;

class EmptyState extends Component
{
    /**
     * The empty state title.
     *
     * @var string
     */
    public $title;

    /**
     * The empty state description text.
     *
     * @var string
     */
    public $text;

    /**
     * A Font Awesome icon for the empty state.
     *
     * @var string
     */
    public $icon;

    /**
     * A button text for an optional CTA.
     *
     * @var string
     */
    public $buttonText;

    /**
     * An URL for the optional CTA button.
     *
     * @var string
     */
    public $buttonUrl;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $title = null, $text = null, $icon = null,
        $buttonText = null, $buttonUrl = null
    ) {
        $this->title = UtilsHelper::applyHtmlEntityDecoder($title);
        $this->text = UtilsHelper::applyHtmlEntityDecoder($text);
        $this->icon = $icon;
        $this->buttonText = $buttonText;
        $this->buttonUrl = $buttonUrl;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('adminlte::components.widget.empty-state');
    }
}
