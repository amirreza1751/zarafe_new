<?php

namespace App\Http\Controllers\API;

use App\Reward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RewardController extends Controller
{
    public function index()
    {
        return Reward::orderBy('rank', 'DESC')->get();
    }
}
