<?php

namespace App\Http\Middleware;

use Closure;

class RedirectorMiddleware
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
        // $fullurl = \Request::url();
        // $url = \App\Redirector::where("old_url", $fullurl)->first();
        // if (!empty($url)) {
        //     return redirect($url->new_url);
        // }
        return $next($request);   
    }
}
