<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    /**
     * Show the collections settings page — how dues are billed and how
     * homeowners pay.
     *
     * @return \Illuminate\View\View
     */
    public function collections()
    {
        $this->authorize('viewAny', \App\Models\Setting::class);

        return view('admin.settings.collections');
    }

    /**
     * Show the registration settings page.
     *
     * @return \Illuminate\View\View
     */
    public function authentication()
    {
        $this->authorize('viewAny', \App\Models\Setting::class);

        return view('admin.settings.authentication');
    }

    /**
     * Show the general settings page.
     *
     * @return \Illuminate\View\View
     */
    public function general()
    {
        $this->authorize('viewAny', \App\Models\Setting::class);

        return view('admin.settings.general');
    }

    /**
     * Show the application profile settings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        $this->authorize('edit', \App\Models\Admin::class);

        return view('admin.settings.profile');
    }

    /**
     * Show the security settings page.
     *
     * @return \Illuminate\View\View
     */
    public function security()
    {
        $this->authorize('update', \App\Models\Setting::class);

        return view('admin.settings.security');
    }

    /**
     * Show the print settings page — the shared letterhead, footer, paper and
     * signatories every printable document draws.
     *
     * @return \Illuminate\View\View
     */
    public function print()
    {
        $this->authorize('print.settings.view');

        return view('admin.settings.print');
    }

    /**
     * Show the database backup page.
     *
     * @return \Illuminate\View\View
     */
    public function backup()
    {
        $this->authorize('update', \App\Models\Setting::class);

        return view('admin.settings.backup');
    }
}
