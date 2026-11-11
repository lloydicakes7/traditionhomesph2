<?php

namespace App\View;

use App\Utility\MenuHelper;
use JeroenNoten\LaravelAdminLte\AdminLte;

/**
 * The panel's AdminLTE service — the package's, with one rule added to the menu
 * it serves: a section an officer's role emptied does not print its title.
 *
 * The package offers no hook after a menu is compiled (a filter sees one item
 * at a time and cannot know what follows a header), so the app binds this class
 * over the package's own in AppServiceProvider::register(), where the package
 * binds its plain AdminLte singleton — see the AdminLTE service provider.
 */
class AdminLteMenu extends AdminLte
{
    /**
     * Get all menu items, or a specific set of them.
     *
     * @param  string|null  $sectionToken  A token representing a section of items
     * @return array
     */
    public function menu($sectionToken = null)
    {
        return MenuHelper::withoutEmptySections(parent::menu($sectionToken));
    }
}
