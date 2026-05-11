<?php

namespace App\Http\Middleware;

use Closure;

class IpWhitelist
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
        $list = [];
        
        $ip_whitelist = env('IP_WHITELIST');
        if (!empty($ip_whitelist)) {
            $list = explode(",", $ip_whitelist);
        }
        if (in_array($request->ip(), $list)) {
            return $next($request);
        } else {
            return json([
                'code' => '403',
                'message' => 'Forbidden IP ('.$request->ip().')'
            ]);
        }
    }
}
