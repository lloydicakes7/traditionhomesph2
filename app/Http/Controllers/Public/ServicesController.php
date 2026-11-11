<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Queries\Public\ServicesQuery;

class ServicesController extends Controller
{
    public function index(ServicesQuery $query)
    {
        return view('public.services', $query->page());
    }
}
