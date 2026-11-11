<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Queries\Portal\ManualQuery;

/**
 * The homeowner portal's User Manual — one page per portal page.
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
