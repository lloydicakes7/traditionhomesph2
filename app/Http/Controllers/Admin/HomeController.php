<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Queries\Admin\DashboardQuery;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(DashboardQuery $query)
    {
        return view('admin.home', [
            'summary'    => $query->summary(),
            'charts'     => $query->charts(),
            'violations' => $query->recentViolations(),
            'requests'   => $query->recentRequests(),
        ]);
    }
}
