<?php

namespace App\Http\Controllers\API;

use App\Score;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
    public function leader_board()
    {
        $leader_board = Score::with('user')->orderBy('score', 'DESC')->get();
        return response()->json($leader_board, 200);
    }
}