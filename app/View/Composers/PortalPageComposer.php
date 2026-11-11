<?php

namespace App\View\Composers;

use Illuminate\View\View;

class PortalPageComposer
{
    /**
     * Bind the data into the client layout.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view): void
    {
        $view->with('portalMenu', $this->menu());
    }

    /**
     * Resolve the configured portal menu.
     *
     * @return array
     */
    protected function menu(): array
    {
        return array_map([$this, 'resolve'], config('portal.menu', []));
    }

    /**
     * Resolve one menu item: a section header, a link, or a submenu whose
     * children are resolved the same way.
     *
     * @param  array  $item
     * @return array
     */
    protected function resolve(array $item): array
    {
        // A header and the sidebar's search box are the menu's own furniture,
        // not pages: neither carries a link, and neither is resolved.
        if (isset($item['header']) || isset($item['type'])) {
            return $item;
        }

        if (! empty($item['submenu'])) {
            $item['submenu'] = array_map([$this, 'resolve'], $item['submenu']);
            $item['active'] = $this->anyActive($item['submenu']);

            return $item;
        }

        $item['href'] = $this->href($item);
        $item['active'] = $this->isCurrent($item);

        return $item;
    }

    /**
     * The link target of a menu item.
     *
     * An item with neither a route nor a url is a module the portal declares
     * but has not built yet. It stays in the sidebar and renders as `#`, which
     * is how the package renders the admin menu's own unbuilt entries, so the
     * menu never offers a route the portal does not publish.
     *
     * @param  array  $item
     * @return string
     */
    protected function href(array $item): string
    {
        if (! empty($item['route'])) {
            return route($item['route']);
        }

        if (! empty($item['url'])) {
            return url($item['url']);
        }

        return '#';
    }

    /**
     * Whether the current request is the page a menu item points at.
     *
     * @param  array  $item
     * @return bool
     */
    protected function isCurrent(array $item): bool
    {
        if (! empty($item['route'])) {
            return request()->routeIs($item['route']);
        }

        return ! empty($item['url']) && url()->current() === url($item['url']);
    }

    /**
     * Whether any item of a submenu is the current page — a submenu holding the
     * current page opens, the way the admin panel's treeview does.
     *
     * @param  array  $items
     * @return bool
     */
    protected function anyActive(array $items): bool
    {
        foreach ($items as $item) {
            if (! empty($item['active'])) {
                return true;
            }
        }

        return false;
    }
}
