<?php

namespace App\Http\Controllers\API;

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
            $user->name = $request->name;
            $user->lname = $request->lname;
        } else {
            $user->name = $request->name;
            $user->lname = $request->lname;
        }
        $user->save();
        return response()->json([
            'status' => '200',
            'message' => 'user successfully updated.'
        ]);

    }
}
