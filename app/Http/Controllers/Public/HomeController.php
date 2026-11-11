<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Queries\Public\HomepageQuery;

class HomeController extends Controller
{
    public function index(HomepageQuery $query)
    {
        return view('public.home', $query->page());
    }
}
