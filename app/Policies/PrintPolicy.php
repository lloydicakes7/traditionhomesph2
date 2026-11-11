<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;

/**
 * The Print Documents module and its settings.
 *
 * A printable document is a view of other records rather than a record of its
 * own, so — like the Reports suite — there is no model for a policy to hang
 * off. This policy is registered as the four gates `print.viewAny`,
 * `print.export`, `print.settings.view` and `print.settings.update` (see
 * \App\Providers\AuthServiceProvider), which is what the pages, the PDF route
 * and the sidebar's menu entries authorize against. Every answer is a module
 * permission read through `Admin::hasPermission()`, so printing is gated the
 * same way the rest of the panel is.
 */
class PrintPolicy
{
    /** The module that owns printing a document. */
    const DOCUMENTS = 'Print Documents';

    /** The module that owns the shared letterhead and footer. */
    const SETTINGS = 'Print Settings';

    /**
     * Determine whether the admin can open the print pages.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermission(self::DOCUMENTS, 'View');
    }

    /**
     * Determine whether the admin can print and download a document.
     */
    public function export(Admin $admin): bool
    {
        return $admin->hasPermission(self::DOCUMENTS, 'Print');
    }

    /**
     * Determine whether the admin can read the print settings page.
     */
    public function viewSettings(Admin $admin): bool
    {
        return $admin->hasPermission(self::SETTINGS, 'View');
    }

    /**
     * Determine whether the admin can change the print settings.
     */
    public function updateSettings(Admin $admin): bool
    {
        return $admin->hasPermission(self::SETTINGS, 'Update');
    }
}
