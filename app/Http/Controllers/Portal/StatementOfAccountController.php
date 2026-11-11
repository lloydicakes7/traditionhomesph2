<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class StatementOfAccountController extends Controller
{
    /**
     * Show the signed-in homeowner's statement of account — what is still to
     * be paid, and the way to pay it.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.statement-of-account');
    }
}
