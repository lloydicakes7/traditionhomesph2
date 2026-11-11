<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Queries\Admin\ManualQuery;

/**
 * The admin panel's User Manual — one page per tool the officer may open.
 */
class ManualController extends Controller
{
    /**
     * Show the manual, optionally at a named topic.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(ManualQuery $manual, ?string $topic = null)
    {
        $page = $manual->page($topic);

        abort_unless($page['active'], 404);

        return view('adminlte::manual.page', $page);
    }
}
