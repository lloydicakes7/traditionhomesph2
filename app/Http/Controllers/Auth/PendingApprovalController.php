<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

/**
 * The notice a registration waits behind until the office opens the account.
 */
class PendingApprovalController extends Controller
{
    public function show()
    {
        return view('auth.pending-approval');
    }
}
