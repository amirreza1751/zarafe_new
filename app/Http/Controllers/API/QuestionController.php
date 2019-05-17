<?php

namespace App\Http\Controllers\API;

use App\Question;
use App\TonightQuestion;
use App\UsedQuestion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
    public function prepare_questions()
    {
        $user_id = auth('api')->user()->id;
        $check_for_answer_limitation = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('created_at', Carbon::today())->count();
        if ($check_for_answer_limitation >=10){
            return response()->json([
                'status' => '200',
                'message' => 'you have answered all of your questions today.'
            ],200);
        }
        $counter = 0;
        $prepared_questions = TonightQuestion::where('user_id', $user_id)->where('used', '0')->whereDate('created_at', Carbon::today())->count();
        if (TonightQuestion::where('user_id', $user_id)->where('used', '0')->whereDate('created_at', Carbon::today())->count() == 10){
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

        if (TonightQuestion::where('user_id', $user_id)->where('used', '0')->whereDate('created_at', Carbon::today())->count() < 10){
            $number = TonightQuestion::where('user_id', $user_id)->where('used', '0')->whereDate('created_at', Carbon::today())->count();
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
        $time = round(microtime(true) * 1000);
        $user_id = auth('api')->user()->id;
//        $user_id = 1;
        $question = TonightQuestion::with('question')
            ->where('user_id', $user_id)
            ->where('used', '0')->whereDate('created_at', Carbon::today())->first();
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

    public function send_answer(Request $request)
    {
        $time = round(microtime(true) * 1000);
        $user_id = auth('api')->user()->id;
        $question_id = $request->question_id;
        $answer = $request->answer;

        $result = TonightQuestion::with('question.question_time')
            ->where('user_id', $user_id)
            ->where('question_id', $question_id)->first();
        if ($result == null){
            return response()->json([
                'status' => '400',
                'message' => 'bad request'
            ], 400);
        }

        if ($time - $result->time > $result->question->question_time->time*1000){ /** out of time. */

            return response()->json([
                'answer time' => $time,
                'question send time' => $result->time,
                'subtract' => $time - $result->time,
                'question time out' => $result->question->question_time->time*1000,
                'status' => 'timeout'
            ]);
        } elseif ($answer == $result->question->correct_answer){ /** in-time answer. now check the answer */
                return response()->json([
                    'answer time' => $time,
                    'question send time' => $result->time,
                    'subtract' => $time - $result->time,
                    'question time out' => $result->question->question_time->time*1000,
                    'status' => 'correct answer'
                ]);
            } else{
            return response()->json([
                'answer time' => $time,
                'question send time' => $result->time,
                'subtract' => $time - $result->time,
                'question time out' => $result->question->question_time->time*1000,
                'status' => 'incorrect answer'
            ]);
        }

    }

    public function test(Request $request)
    {
return Carbon::now();
        //        return Question::where('id', '>', '4')->whereDate('created_at', Carbon::today())->get();
        $user_id = 11;
        $prepared_questions = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('created_at', Carbon::today())->count();
        return $prepared_questions;
    }
}