<?php

namespace App\Http\Controllers\API;

use App\Score;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
    public function leaderboard()
    {
        $leader_board = Score::with('user')->orderBy('score', 'DESC')->paginate(10);
        return response()->json($leader_board, 200);
    }
}
