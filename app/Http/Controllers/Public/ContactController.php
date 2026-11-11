<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Queries\Public\ContactQuery;

class ContactController extends Controller
{
    public function index(ContactQuery $query)
    {
        return view('public.contact', $query->page());
    }
}
