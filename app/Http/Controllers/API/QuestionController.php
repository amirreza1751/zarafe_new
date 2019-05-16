<?php

namespace App\Http\Controllers\API;

use App\Question;
use App\TonightQuestion;
use App\UsedQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    public function prepare_questions()
    {
        $user_id = auth('api')->user()->id;
//            $user_id = 1;
            $counter = 0;
        $prepared_questions = TonightQuestion::where('user_id', $user_id)->where('used', '0')->count();
        if (TonightQuestion::where('user_id', $user_id)->where('used', '0')->count() == 10){
            return response()->json([
                'status' => '200',
                'message' => 'tonight questions have been created.'
            ],200);
        }

        for ($i=0;$i<10-$prepared_questions;$i++){


            $question = Question::inRandomOrder()->first(); /** pick a random question */
//            echo "user:".$user_id."_"."question:".$question->id;
//            echo "1";
            while (UsedQuestion::where('user_id',$user_id)->where('question_id', $question->id)->exists()){
                $question = Question::inRandomOrder()->first(); /** pick a random question */
//                echo "while question id:".$question->id;
                $counter++;
                if ($counter==50){
                    break;
                }
            }
            if ($counter==50){
                break;
            }
//            echo "afterloop";
            UsedQuestion::create([
                'user_id' => $user_id,
                'question_id' => $question->id
            ]);
            TonightQuestion::create([
                'user_id' => $user_id,
                'question_id' => $question->id,
                'used' => '0'
            ]);
        }

        if (TonightQuestion::where('user_id', $user_id)->where('used', '0')->count() < 10){
            $number = TonightQuestion::where('user_id', $user_id)->where('used', '0')->count();
            return response()->json([
                'status' => '120',
                'message' => 'only '. $number . ' question(s) found.',
                'count' => $number
            ], 200);
        } else {
            return response()->json([
                'status' => '200',
                'message' => 'tonight questions are ready.'
            ], 200);
        }

    }

    public function get_question()
    {
        $time = time();
//        $user_id = auth('api')->user()->id;
        $user_id = 1;
        $question = TonightQuestion::with('question')
            ->where('user_id', $user_id)
            ->where('used', '0')->first();
        if ($question == null){
            return response()->json([
                'status' => '400',
                'message' => 'no question found.'
            ], 400);
        }
        $question->time = $time;
        $question->used = '1';
        $question->save();
        $result = $question->question;
        unset($result['correct_answer']);

        return response()->json($result, 200);
    }
}
