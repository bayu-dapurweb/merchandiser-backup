<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class ApiAuth
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $header = $request->header();

        $session_token = get('token', $request->token);        

        if (!empty($header['authorization'][0]) || !empty($session_token)) {
            $token = substr($header['authorization'][0], 7);
            
            if (!empty($session_token)) {
                $token = $session_token;
            }
            if ($token === false) {
                return plainjson([
                    'code'      => 401,
                    'message'   => 'Unauthorized',
                    'data'      => [
                        "title"     => "Unauthorized",
                        "detail"    => "Full authentication is required to access this resource",
                    ]                
                ]);
            }
            
            $user_token = \App\UserToken::where('jwt_token' , $token)->where("firebase_token", "!=", "reset-password")->first();
            
            if (!empty($user_token)) {
                $user = \App\RefUsers::with("profilepicture")->find($user_token->user_id);
                $request->user = $user;
                return $next($request);
            } else {
                return plainjson([
                    'code'      => 401,
                    'message'   => 'Unauthorized',
                    'data'      => [
                        "title"     => "Unauthorized",
                        "detail"    => "Full authentication is invalid to access this resource",
                    ]
                ]);
            }
        } else {
            return plainjson([
                'code'      => 401,
                'message'   => 'Unauthorized',
                'data'      => [
                    "title"     => "Unauthorized",
                    "detail"    => "Full authentication is required to access this resource",
                ]                
            ]);
        }

        if ($this->auth->guard($guard)->guest()) {
            return plainjson([
                'code'      => 401,
                'message'   => 'Unauthorized',
                'data'      => [
                    "title"     => "Unauthorized",
                    "detail"    => "Full authentication is required to access this resource",
                ]
            ]);
        }
    }
}
