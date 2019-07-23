<?php

namespace App\Http\Controllers;

use App\PhonenumberToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
//        return $request->phone_number;
        $request->validate([
            'phone_number' => 'required|regex:/(0)[0-9]{10,15}/',
            'token' => 'required',
        ]);

        $record = PhonenumberToken::where('phone_number',$request->phone_number)->where('used','0')->latest()->first();
        if ($record == null){
            return response()->json([
                'status' => 'otp has used before or does not exist.'
            ],400);
        }

        if($record->token != $request->token){
            return response()->json([
                'status' => 'otp code is incorrect.'
            ],400);
        }

        $check_user = User::where('phone_number', $request->phone_number)->first(); /**  age user vojud dasht dg nemisazesh va mostaghim login ro seda mizane. */
        if (isset($check_user)){
//            return response()->json(['status'=>'duplicate user', 'description'=> 'already registered. please sign in.'], 200);
            $created_user = [
                'phone_number' => $check_user->phone_number,
//            'remember_me' => '1'
            ];

            $new_request = new \Illuminate\Http\Request();
            $new_request->replace($created_user);
            $login_response = app('App\Http\Controllers\AuthController')->login($new_request);

            $record->used = '1';
            $record->save();

            return $login_response;
        }

//        $user = new User([
//            'phone_number' => $request->phone_number,
//        ]);
//
//        $user->save();
        $user = User::create([
            'phone_number' => $request->phone_number,
            'username' => app('App\Http\Controllers\AuthController')->random_username_generator(15)
        ]);
        $created_user = [
            'phone_number' => $user->phone_number,
//            'remember_me' => '1'
        ];

        $new_request = new \Illuminate\Http\Request();
        $new_request->replace($created_user);
        $login_response = app('App\Http\Controllers\AuthController')->login($new_request);

        $record->used = '1';
        $record->save();

        return $login_response;
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
//            'remember_me' => 'boolean'
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if($user == null)
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        /** create_user_score() creates this user score if is not created. */
        app('App\Http\Controllers\API\QuestionController')->create_user_score($user->id);


        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(300);

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }


    public function random_username_generator($n) {
        $characters = '123456789abcdefghijklmnopqrstuvwxyz_';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = mt_rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }
}