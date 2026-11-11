<?php

namespace App\Utility;

use JeroenNoten\LaravelAdminLte\Helpers\MenuItemHelper;

/**
 * Operations on a compiled AdminLTE menu.
 *
 * The menu is a flat list in which a header item introduces the items after it
 * until the next header — the panel's "OPERATIONS", "FINANCE", "REPORTS AND
 * SUPPORT" and "ADMINISTRATION" sections. No business meaning lives here: this
 * is what any Laravel app on this package would want from its sidebar.
 */
class MenuHelper
{
    /**
     * Drop every section header that nothing follows.
     *
     * The package already withholds an item an officer's role may not open, and
     * a submenu whose every child was withheld along with it. A header is
     * nobody's permission, though, so a section the policies emptied still
     * printed its title — hanging over nothing.
     *
     * A header stands over the items after it until the next header (or the end
     * of the menu), so it is kept only where one of those items is there to
     * stand over. A run of headers therefore loses all but its last, and a
     * title at the end loses itself.
     *
     * @param  array<int, mixed>  $items  A compiled menu's items
     * @return array<int, mixed>
     */
    public static function withoutEmptySections(array $items): array
    {
        // The package hands its sections over filtered, not renumbered.
        $items = array_values($items);

        $kept = [];

        foreach ($items as $index => $item) {
            if (MenuItemHelper::isHeader($item) && ! self::standsOverSomething($items, $index)) {
                continue;
            }

            $kept[] = $item;
        }

        return $kept;
    }

    /**
     * Whether anything stands under the header at the given position — an item
     * before the next header rather than another header.
     *
     * @param  array<int, mixed>  $items
     */
    private static function standsOverSomething(array $items, int $index): bool
    {
        return isset($items[$index + 1]) && ! MenuItemHelper::isHeader($items[$index + 1]);
    }
}
