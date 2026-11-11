<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class ComplaintController extends Controller
{
    /**
     * Show the homeowner's complaints and the office's responses.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.complaints');
    }
}
