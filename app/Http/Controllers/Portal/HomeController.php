<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Queries\Portal\DashboardQuery;

class HomeController extends Controller
{
    public function index(DashboardQuery $query)
    {
        /** @var User $account */
        $account = auth('homeowner')->user();

        return view('portal.home', $query->overview($account));
    }
}
