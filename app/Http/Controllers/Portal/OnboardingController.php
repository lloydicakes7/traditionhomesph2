<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * The one page a resident can open until the office has verified their profile.
 */
class OnboardingController extends Controller
{
    /**
     * Show the onboarding form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $this->authorize('updateOwn', Auth::guard('homeowner')->user());

        return view('portal.onboarding');
    }
}
