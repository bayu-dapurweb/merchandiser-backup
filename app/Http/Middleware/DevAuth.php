<?php

namespace App\Http\Middleware;

use Closure;

class DevAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Keep developer routes frictionless in local/testing only.
        if (app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        if ((string) session('developer') === 'yes') {
            return $next($request);
        }

        $allowedIps = array_filter(array_map('trim', explode(',', (string) env('DEV_AUTH_ALLOWED_IPS', ''))));
        if (!empty($allowedIps) && in_array($request->ip(), $allowedIps, true)) {
            return $next($request);
        }

        abort(403, 'Developer access is restricted.');
    }
}
