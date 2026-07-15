<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class QueryLoggingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isLocal() && ! config('app.debug')) {
            return $next($request);
        }

        DB::listen(function ($query): void {
            if ($query->time > 100) {
                Log::warning('SLOW_QUERY', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms' => $query->time,
                    'url' => request()?->fullUrl(),
                    'method' => request()?->method(),
                ]);
            }
        });

        return $next($request);
    }
}
