<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DemoEmailVerificationController extends Controller
{
    /**
     * Bypass email verification for demo purposes.
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(config('fortify.home'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(config('fortify.home'))->with('status', 'Email successfully verified automatically for demo mode.');
    }
}
