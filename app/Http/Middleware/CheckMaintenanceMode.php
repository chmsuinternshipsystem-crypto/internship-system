<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    protected array $except = [
        'maintenance',
        'login',
        'login.store',
        'student.otp.show',
        'student.otp.verify',
        'student.otp.resend',
        'password.request',
        'password.email',
        'password.reset',
        'password.store',
        'student.logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs($this->except)) {
            return $next($request);
        }

        $maintenance = Setting::campus()->maintenance_mode ?? false;

        if (! $maintenance) {
            return $next($request);
        }

        $user = $request->user();

        if ($user) {
            if ($user instanceof \App\Models\User && in_array($user->role, ['admin', 'chairperson', 'dean', 'instructor'], true)) {
                return $next($request);
            }
        }

        return redirect()->route('maintenance');
    }
}
