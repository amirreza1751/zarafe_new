<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function view_profile()
    {
        $user = auth('api')->user();
        return $user;
    }
}
