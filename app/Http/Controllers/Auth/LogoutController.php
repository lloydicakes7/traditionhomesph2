<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SignOutAccount;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Sign out whichever account holds the session.
 */
class LogoutController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, SignOutAccount $signOut)
    {
        $signOut->handle((string) $request->ip());

        return redirect()->route('login');
    }
}
