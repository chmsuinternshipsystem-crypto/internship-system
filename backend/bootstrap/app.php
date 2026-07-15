<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureStudentSession;
use App\Http\Middleware\PreventHtmxCaching;
use App\Http\Middleware\QueryLoggingMiddleware;
use App\Http\Middleware\RequestTimingMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            RequestTimingMiddleware::class,
            QueryLoggingMiddleware::class,
            CheckMaintenanceMode::class,
            PreventHtmxCaching::class,
        ]);

        // Alias for simple role-based access control, e.g. ->middleware('role:admin,coordinator')
        $middleware->alias([
            'auth' => \App\Http\Middleware\CaptureAuth::class,
            'role' => RoleMiddleware::class,
            'student.auth' => EnsureStudentSession::class,
            // Same behavior as Laravel's `guest` alias: allow route only when web guard has no user.
            // Capstone actors are students, coordinator, chair, and partners — not a "guest user" role.
            'staff_login_entry' => RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Standardized error pages for common HTTP failures (NOT for ValidationException or other non-HTTP exceptions).
        $exceptions->renderable(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            $statusCode = $e->getStatusCode();

            $supported = [403, 404, 419, 429, 500];
            if (! in_array($statusCode, $supported, true)) {
                return $statusCode >= 500 && app()->isProduction()
                    ? response()->view('errors.500', [], $statusCode)
                    : null;
            }

            if (! view()->exists("errors.{$statusCode}")) {
                return null;
            }

            if (! app()->isProduction() && $statusCode >= 500) {
                return null;
            }

            return response()->view("errors.{$statusCode}", [], $statusCode);
        });
    })->create();
