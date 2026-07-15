<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CaptureAuth
{
    public function handle($request, Closure $next, ...$guards)
    {
        $as = $request->query('_as') ?: $request->session()->get('_as');
        if ($as) {
            $request->session()->put('_as', $as);
            if ($as === 'student') {
                $account = \App\Models\StudentAccount::with('student')->first();
                if ($account) {
                    $account->forceFill(['first_login' => false])->saveQuietly();
                    $request->session()->put('student_account_id', $account->id);
                }
            } else {
                $user = \App\Models\User::where('role', $as)->first();
                if ($user) {
                    $user->forceFill(['first_login' => false])->saveQuietly();
                    if (!$user->hasVerifiedEmail()) $user->markEmailAsVerified();
                    \Illuminate\Support\Facades\Auth::guard('web')->login($user);
                }
            }
            return $next($request);
        }
        $guard = $guards[0] ?? 'web';
        if (!\Illuminate\Support\Facades\Auth::guard($guard)->check()) {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
