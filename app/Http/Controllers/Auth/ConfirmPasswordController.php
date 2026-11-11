<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ConfirmPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use Illuminate\Http\Request;

/**
 * The screen and the check both panels share when a protected action asks an
 * account to repeat its password, each panel on its own guard.
 */
abstract class ConfirmPasswordController extends Controller
{
    /**
     * Show the confirmation form.
     *
     * @return \Illuminate\View\View
     */
    public function showConfirmForm()
    {
        return view($this->formView());
    }

    /**
     * Confirm the password, then continue to where the account was going.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function confirm(ConfirmPasswordRequest $request, ConfirmPassword $confirm)
    {
        if (! $confirm->handle($this->guardName(), (string) $request->validated()['password'])) {
            return back()->withErrors(['password' => __('auth.confirm_failed')]);
        }

        return $request->wantsJson()
            ? response()->json([], 204)
            : redirect()->intended($this->home());
    }

    /**
     * The guard the panel's accounts sign in on.
     */
    abstract protected function guardName(): string;

    /**
     * Where a confirmed account lands when no intended URL is waiting.
     */
    abstract protected function home(): string;

    /**
     * The confirmation view this panel shows.
     */
    abstract protected function formView(): string;
}
