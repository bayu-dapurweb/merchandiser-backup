<?php 
namespace App\Service;

use Illuminate\Support\Facades\Hash;

class AuthService
{

    public static function generateToken($user, $fcm_token = "", $google_token = "", $apple_token = "")
    {
        /*  single sign-on  */
        if ($user->id > 1) {
            $tokens = \App\UserToken::where("user_id", $user->id)->get();
            foreach ($tokens as $token) {
                // $token->delete();
            }
        }
        
        /* create new token */
        $token = encrypt_string(Hash::make(json_encode($user)));
        $user_token = new \App\UserToken;
        $user_token->user_id = $user->id;
        $user_token->jwt_token = $token;
        $user_token->firebase_token = $fcm_token;
        $user_token->save();
        return $user_token;
    }
}