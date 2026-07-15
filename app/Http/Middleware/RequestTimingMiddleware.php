<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequestTimingMiddleware
{
    /**
     * Add lightweight timing headers for request diagnostics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestStart = microtime(true);
        $queryCount = 0;
        $dbTimeMs = 0.0;

        $listener = DB::listen(function (QueryExecuted $query) use (&$queryCount, &$dbTimeMs): void {
            $queryCount++;
            $dbTimeMs += (float) $query->time;
        });

        /** @var Response $response */
        $response = $next($request);

        // Best effort cleanup where available.
        if (method_exists(DB::getFacadeRoot(), 'forgetRecordModificationState')) {
            DB::forgetRecordModificationState();
        }

        $totalMs = (microtime(true) - $requestStart) * 1000;
        $response->headers->set('X-Request-Time-Ms', (string) round($totalMs, 2));
        $response->headers->set('X-DB-Time-Ms', (string) round($dbTimeMs, 2));
        $response->headers->set('X-DB-Query-Count', (string) $queryCount);

        return $response;
    }
}
