<?php

namespace App\Http\Middleware;

use Closure;

class WebAuth
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
        $login = session("login");    
        $user_token = \App\UserToken::where('jwt_token' , $login['id_token'])->first();

        if(!empty($user_token)){
            $user = \App\User::find($user_token->user_id);
            $request->user = $user;
            return $next($request);   
        }

        return response()->view('web/auth/unautorized');
        
    }

    public function handle_and_shit($request, Closure $next)
    {
        $login = session("login");    
        $user_token = \App\UserToken::where('jwt_token' , $login['id_token'])->first();
        // dd($user_token);
        // if (!empty($user_token)) {
            $user = \App\User::find($user_token->user_id);
            $request->user = $user;
            return $next($request);
        // } else {
        //     // return route("get.unautorized");
        // }

        // if (!empty($login['id_token'])) {
        //     $token = substr($header['authorization'][0], 7);
        //     $user_token = \App\UserToken::where('jwt_token' , $login['id_token'])->first();
        //     if (!empty($user_token)) {
        //         $user = \App\User::find($user_token->user_id);
        //         $request->user = $user;
        //         return $next($request);
        //     } else {
        //         return route("get.unautorized");
        //     }
        // } else {
        //     return route("get.unautorized");
        // }

        // if ($this->auth->guard($guard)->guest()) {
        //     return route("get.unautorized");
        // }
    }
}
