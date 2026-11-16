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
        $account = Auth::guard('homeowner')->user();

        $this->authorize('updateOwn', $account);

        if ($account->profile_status?->isSubmitted()) {
            return redirect()->route('portal.dashboard')->with('notify', [
                'type'    => 'info',
                'message' => __('auth.onboarding.under_review'),
            ]);
        }

        return view('portal.onboarding');
    }
}
