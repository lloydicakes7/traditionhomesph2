<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Directory\DirectoryEntry;
use App\Models\Announcement;
use App\Models\Content;
use App\Models\Faq;
use App\Models\HoaMember;

class HOACenterController extends Controller
{
    /**
     * Show the HOA Center members page.
     *
     * @return \Illuminate\View\View
     */
    public function members()
    {
        $this->authorize('viewAny', HoaMember::class);

        return view('admin.hoa-center.members');
    }

    /**
     * Show the HOA Center announcements page.
     *
     * @return \Illuminate\View\View
     */
    public function announcements()
    {
        $this->authorize('viewAny', Announcement::class);

        return view('admin.hoa-center.announcements');
    }

    /**
     * Show the HOA Center directory page.
     *
     * @return \Illuminate\View\View
     */
    public function directory()
    {
        $this->authorize('viewAny', DirectoryEntry::class);

        return view('admin.hoa-center.directory');
    }

    public function faqs()
    {
        $this->authorize('viewAny', Faq::class);

        return view('admin.hoa-center.faqs');
    }

    public function contents()
    {
        $this->authorize('viewAny', Content::class);

        return view('admin.hoa-center.contents');
    }
}
