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
        return $next($request);

        // $dev = \Session::get("developer");        
        
        // $host = "sinergiacademy.com";

        // $ip = $request->ip();

        // if ($ip == "178.128.29.139") {
        //     return $next($request);
        // }
        
        // if (!empty($_SERVER['SERVER_NAME']) 
        //     && (
        //         $_SERVER['SERVER_NAME'] == "$host" 
        //     ) 
        //     && $dev != "yes"            
        //     && $_SERVER['REQUEST_URI'] != "/password"
        // ) {
        //     return response()->view("web/devpass");
        //     // route("get.dev.password");
        // } else {
        //     return $next($request);
        // }
    }
}
