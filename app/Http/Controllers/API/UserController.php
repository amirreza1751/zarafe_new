<?php

namespace App\Http\Controllers\API;

use App\Score;
use App\UsedQuestion;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function view_profile()
    {
        $user = auth('api')->user();
        unset(
            $user['role_id'],
            $user['email'],
            $user['created_at'],
            $user['updated_at'],
            $user['settings']
        );
        return $user;
    }

    public function edit_profile(Request $request)
    {
        $user = auth('api')->user();
//        $user = User::find(14);
        $request->validate([
            'username' => 'unique:users,username,'.$user->id
        ]);
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('/public/avatars');
            $path = str_replace("public/","",$path);
            $user->avatar = $path;
            if(isset($request->name)) $user->name = $request->name;
            if(isset($request->lname)) $user->lname = $request->lname;
            if(isset($request->username)) $user->username = $request->username;
        } else {
            if(isset($request->name)) $user->name = $request->name;
            if(isset($request->lname)) $user->lname = $request->lname;
            if(isset($request->username)) $user->username = $request->username;
        }
        $user->save();
        return response()->json([
            'status' => '200',
            'message' => 'user successfully updated.'
        ]);

    }

    public function check_invite_code(Request $request)
    {
        $request->validate([
            'data' => 'required'
        ]);
        $invited_user = auth('api')->user();
        if ($invited_user->is_invited == '1'){
            return response()->json([
                'status' => '400',
                'message' => 'Bad request.'
            ]);
        }

        $inviter = User::where('phone_number', $request->get('data'))->orWhere('username', $request->get('data'))->first();
        if ($inviter != null && $inviter->phone_number != $invited_user->phone_number){
            $score = Score::where('user_id', $inviter->id)->where('competition_id', '1')->first();
            if ($score != null){
                $score->score += 3;
                $score->save();
            } else
                return response()->json([
                    'status' => '404',
                    'message' => 'Inviter not found.'
                ]);
        } else
                return response()->json([
                    'status' => '404',
                    'message' => 'Inviter not found.'
                ]);

        $invited_user->is_invited = '1';
        $invited_user->save();

        return response()->json([
            'status' => '200',
            'message' => 'Inviter rewarded.'
        ]);
    }


}
