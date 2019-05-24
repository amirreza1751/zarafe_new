<?php

namespace App\Http\Controllers\API;

use App\Question;
use App\Result;
use App\Score;
use App\TonightQuestion;
use App\UsedQuestion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

class QuestionController extends Controller
{
    public function prepare_questions()
    {
//        $user_id = auth('api')->user()->id;
        $user_id = 11;
        /** create_user_score() creates this user score if is not created. */
        app('App\Http\Controllers\QuestionController')->create_user_score($user_id);
        $check_for_answer_limitation = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('updated_at', Carbon::today())->count();
        if ($check_for_answer_limitation >=10){
            return response()->json([
                'status' => '200',
                'message' => 'you have answered all of your questions today.'
            ],200);
        }
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
            if ($number == 0){
                return response()->json([
                    'status' => '120',
                    'message' => 'there is no question for you.',
                    'count' => $number
                ], 200);
            }
            return response()->json([
                'status' => '120',
                'message' => 'only '. $number . ' question(s) found.',
                'count' => $number
            ], 200);
        } else {
            return response()->json([
                'status' => '200',
                'message' => 'your questions are ready.'
            ], 200);
        }

    }

    public function create_user_score($user_id)
    {
        $user_score = Score::where('user_id', $user_id)->first();
        if ($user_score != null){
            return $user_score;
        } else {
            return Score::create([
                'score' => '0',
                'user_id' => $user_id
            ]);
        }
    }




    public function get_question()
    {
        $time = round(microtime(true) * 1000);
//        $user_id = auth('api')->user()->id;
        $user_id = 11;
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
//        $user_id = auth('api')->user()->id;
        $user_id = 11;
        $question_id = $request->question_id;
        $answer = $request->answer;


        if (Result::where('user_id', $user_id)->where('question_id', $question_id)->exists()){ /** check for duplicate answers */
            return response()->json([
                'status' => '400',
                'message' => 'duplicate answer'
            ]);
        }

        $result = TonightQuestion::with('question.question_time')
            ->where('user_id', $user_id)
            ->where('question_id', $question_id)->first();
        if ($result == null){
            return response()->json([
                'status' => '400',
                'message' => 'bad request'
            ], 400);
        }

        if ($time - $result->time > $result->question->question_time->time*1000 + $result->question->video_length*1000){ /** out of time. */

            return response()->json([
                'answer time' => $time,
                'question send time' => $result->time,
                'subtract' => $time - $result->time,
                'question time out' => $result->question->question_time->time*1000 + $result->question->video_length*1000,
                'status' => 'timeout'
            ]);
        } elseif ($answer == $result->question->correct_answer){ /** in-time answer. now check the answer */
                /** correct answer */
                $user_score = Score::where('user_id', $user_id)->first();
                $user_score->score += $result->question->question_time->score; /** add score to user */
                $user_score->save();

            return Result::create([
                    'user_id' => $user_id,
                    'competition_id' => $result->question->competition_id,
                    'question_id' => $result->question->id,
                    'answer' => $request->answer
                ]);


//                return response()->json([
//                    'answer time' => $time,
//                    'question send time' => $result->time,
//                    'subtract' => $time - $result->time,
//                    'question time out' => $result->question->question_time->time*1000 + $result->question->video_length*1000,
//                    'status' => 'correct answer'
//                ]);
            } else{ /** incorrect answer */

            return Result::create([
                    'user_id' => $user_id,
                    'competition_id' => $result->question->competition_id,
                    'question_id' => $result->question->id,
                    'answer' => $request->answer
                ]);

//                return response()->json([
//                    'answer time' => $time,
//                    'question send time' => $result->time,
//                    'subtract' => $time - $result->time,
//                    'question time out' => $result->question->question_time->time*1000 + $result->question->video_length*1000,
//                    'status' => 'incorrect answer'
//                ]);
        }

    }


    public function results()
    {
        $correct_answers = 0;
        $tonight_score = 0;
        //        $user_id = auth('api')->user()->id;
        $user_id = 11;
        $results = Result::with('question.question_time')->where('user_id', $user_id)->whereDate('created_at', Carbon::today())->get();
        foreach ($results as $result){
            if ($result->answer == $result->question->correct_answer){
                $correct_answers++;
                $tonight_score = $tonight_score + $result->question->question_time->score;
            }
        }
        $total_score = Score::where('user_id', $user_id)->first()->score;
        $rank = Score::where('user_id', $user_id)->first()->getRanking();
        return response()->json([
            'correct_answers' => $correct_answers,
            'tonight_score' => $tonight_score,
            'total_score' => $total_score,
            'rank' => $rank
        ]);

    }

    public function test(Request $request)
    {
//        $score = Score::where('user_id', 11)->first()->getRanking();
//        return $score;

//        return Question::orderBy('id', 'DESC')->get();
//return Carbon::now();
        //        return Question::where('id', '>', '4')->whereDate('created_at', Carbon::today())->get();
        $user_id = 11;
        $prepared_questions = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('created_at', Carbon::today())->count();
        return $prepared_questions;
    }
}