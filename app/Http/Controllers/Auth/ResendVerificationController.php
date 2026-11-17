<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResendEmailVerification;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendVerificationRequest;
use Illuminate\Http\Request;

/**
 * Ask for a new verification link from the sign-in page or an expired link.
 */
class ResendVerificationController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.verify-resend', [
            'verificationEmail' => (string) $request->session()->get('verification_email', ''),
        ]);
    }

    public function store(ResendVerificationRequest $request, ResendEmailVerification $resend)
    {
        $email = (string) $request->validated()['email'];

        $resend->handle($email);

        return redirect()
            ->route('verification.resend')
            ->with('verification_email', $email)
            ->with('status', __('auth.verification_resent'));
    }
}
