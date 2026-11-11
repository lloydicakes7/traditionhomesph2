<?php

namespace App\View\Blades;

use App\View\Components\Adminlte\Form\InputContact;
use App\View\Components\Adminlte\Widget\Accordion;
use App\View\Components\Adminlte\Widget\AccordionItem;
use App\View\Components\Adminlte\Widget\InfoBox;
use App\View\Components\Adminlte\Widget\Status;
use App\View\Components\Adminlte\Widget\Badge;
use App\View\Components\Adminlte\Widget\EmptyState;
use App\View\Components\Adminlte\Widget\Loading;
use App\View\Components\Adminlte\Layout\NavbarNotification;
use App\View\Components\Adminlte\Tool\Tabs;
use App\View\Components\Adminlte\Tool\Dropdown;
use App\View\Components\Adminlte\Tool\DropdownItem;

class AdminLteBlades
{
    public static function component($blade)
    {
        $blade->component(InputContact::class, 'input-contact', 'adminlte');
        $blade->component(InfoBox::class, 'info-box', 'adminlte');
        $blade->component(Accordion::class, 'accordion', 'adminlte');
        $blade->component(AccordionItem::class, 'accordion-item', 'adminlte');
        $blade->component(Status::class, 'status', 'adminlte');
        $blade->component(Badge::class, 'badge', 'adminlte');
        $blade->component(EmptyState::class, 'empty-state', 'adminlte');
        $blade->component(Loading::class, 'loading', 'adminlte');
        $blade->component(Tabs::class, 'tabs', 'adminlte');
        $blade->component(NavbarNotification::class, 'notification-bell', 'adminlte');
        $blade->component(Dropdown::class, 'dropdown', 'adminlte');
        $blade->component(DropdownItem::class, 'dropdown-item', 'adminlte');

        return $blade;
    }
}
