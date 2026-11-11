<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    /**
     * Show the payments the signed-in homeowner has filed.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('portal.payments');
    }
}
