<?php

namespace App\Http\Controllers\API;

use App\Question;
use App\Result;
use App\Score;
use App\TonightQuestion;
use App\UsedQuestion;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;

class QuestionController extends Controller
{
    public function prepare_questions()
    {
        $user_id = auth('api')->user()->id;
//        $user_id = 11;
        /** create_user_score() creates this user score if is not created. */
        app('App\Http\Controllers\API\QuestionController')->create_user_score($user_id);
        $check_for_answer_limitation = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('updated_at', Carbon::today())->count();
        if ($check_for_answer_limitation >=10){
            return response()->json([
                'status' => '111',
                'message' => 'you have answered all of your questions today.'
            ]);
        }
        $counter = 0;
            $prepared_questions = TonightQuestion::where('user_id', $user_id)->whereDate('updated_at', Carbon::today())->count();
        if (TonightQuestion::where('user_id', $user_id)->whereDate('updated_at', Carbon::today())->count() == 10){
            return response()->json([
                'status' => '200',
                'message' => 'your questions are ready.'
            ]);
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
                    'status' => '113',
                    'message' => 'there is no question for you.',
                    'count' => $number
                ]);
            }
            return response()->json([
                'status' => '114',
                'message' => 'only '. $number . ' question(s) found.',
                'count' => $number
            ]);
        } else {
            return response()->json([
                'status' => '200',
                'message' => 'your questions are ready.'
            ]);
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
                'user_id' => $user_id,
                'competition_id' => '1'
            ]);
        }
    }





    public function get_question($question_id)
    {
        $time = round(microtime(true) * 1000);
        $user_id = auth('api')->user()->id;
//        $user_id = 11;
        $question = TonightQuestion::with('question')
            ->where('user_id', $user_id)
            ->where('used', '0')
            ->where('question_id', $question_id)
            ->first();
        if ($question == null){
            return response()->json([
                'status' => '115',
                'message' => 'no question found.'
            ]);
        }
        $question->time = $time;
        $question->used = '1';
        $question->save();
        $result = $question->question;
        unset($result['correct_answer'], $result['created_at'], $result['updated_at'], $result['link_hls'], $result['link_dash']);

        $current_question_number = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('updated_at', Carbon::today())->count();
        $total_prepared_questions = TonightQuestion::where('user_id', $user_id)->where('used', '0')->count() + $current_question_number;

        $result['current_question_number'] = $current_question_number;
        $result['total_prepared_questions'] = $total_prepared_questions;
        $result['status'] = "200";
        return response()->json($result, 200);
    }


    public function initial_user_result($user_id, $question_id, $competition_id)
    {
        if (Result::where('user_id', $user_id)->where('question_id', $question_id)->exists())
        {
            return;
        }
        if ($user_id == null || $question_id == null || $competition_id == null)
            return response()->json([
                'status' => '400',
                'message' => 'Bad request.'
            ]);
        else
            return Result::create([
                'answer' => '0',
                'user_id' => $user_id,
                'competition_id' => $competition_id,
                'question_id' => $question_id
            ]);

    }


    public function get_video()
    {
        $user_id = auth('api')->user()->id;
        $result = TonightQuestion::with('question.question_time')
            ->where('user_id', $user_id)
            ->where('used', '0')->first();
        if ($result == null){
            return response()->json([
                'status' => '115',
                'message' => 'no question found.'
            ]);
        }

        app('App\Http\Controllers\API\QuestionController')->initial_user_result($user_id, $result->question->id, $result->question->competition_id);

        $current_question_number = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('updated_at', Carbon::today())->count();
        $total_prepared_questions = TonightQuestion::where('user_id', $user_id)->where('used', '0')->count() + $current_question_number;

        return response()->json([
            'status' => 200,
            'question_id' => $result->question->id,
            'question_time' => $result->question->question_time->time,
            'link_hls' => $result->question->link_hls,
            'link_dash' => $result->question->link_dash,
            'current_question_number' => $current_question_number,
            'total_prepared_questions' => $total_prepared_questions

        ], 200);
    }





    public function send_answer(Request $request)
    {
        $time = round(microtime(true) * 1000);
        $user_id = auth('api')->user()->id;
//        $user_id = 11;
        $question_id = $request->question_id;
        $answer = $request->answer;


        if (Result::where('user_id', $user_id)->where('question_id', $question_id)->where('answer', '!=', '0')->exists()){ /** check for duplicate answers */
            return response()->json([
                'status' => '116',
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
            ]);
        }

        if ($time - $result->time > $result->question->question_time->time*1000){ /** out of time. */

            return response()->json([
                'answer time' => $time,
                'question send time' => $result->time,
                'subtract' => $time - $result->time,
                'question time out' => $result->question->question_time->time*1000,
                'status' => '117',
                'message' => 'timeout',
                'correct_answer' => $result->question->correct_answer
            ]);
        } elseif ($answer == $result->question->correct_answer){ /** in-time answer. now check the answer */
                /** correct answer */
                $user_score = Score::where('user_id', $user_id)->first();
                $user_score->score += $result->question->question_time->score; /** add score to user */
                $user_score->save();

                $answer_result = Result::where('user_id', $user_id) /** save the answer result */
                    ->where('question_id', $result->question->id)->first();
                $answer_result->answer = $answer;
                $answer_result->save();

//             Result::create([
//                    'user_id' => $user_id,
//                    'competition_id' => $result->question->competition_id,
//                    'question_id' => $result->question->id,
//                    'answer' => $request->answer
//                ]);
             return response()->json([
                 'status' => '118',
                 'message' => 'correct answer',
                 'correct_answer' => $result->question->correct_answer
             ]);


//                return response()->json([
//                    'answer time' => $time,
//                    'question send time' => $result->time,
//                    'subtract' => $time - $result->time,
//                    'question time out' => $result->question->question_time->time*1000,
//                    'status' => 'correct answer'
//                ]);
            } else{ /** incorrect answer */

                    $answer_result = Result::where('user_id', $user_id) /** save the answer result */
                    ->where('question_id', $result->question->id)->first();
                    $answer_result->answer = $answer;
                    $answer_result->save();

//             Result::create([
//                    'user_id' => $user_id,
//                    'competition_id' => $result->question->competition_id,
//                    'question_id' => $result->question->id,
//                    'answer' => $request->answer
//                ]);
            return response()->json([
                'status' => '119',
                'message' => 'incorrect answer',
                'correct_answer' => $result->question->correct_answer
            ]);

//                return response()->json([
//                    'answer time' => $time,
//                    'question send time' => $result->time,
//                    'subtract' => $time - $result->time,
//                    'question time out' => $result->question->question_time->time*1000,
//                    'status' => 'incorrect answer'
//                ]);
        }

    }


    public function results()
    {
        $correct_answers = 0;
        $tonight_score = 0;
                $user_id = auth('api')->user()->id;
//        $user_id = 11;
        $results = Result::with('question.question_time')->where('user_id', $user_id)->whereDate('created_at', Carbon::today())->get();
        foreach ($results as $result){
            if ($result->answer == $result->question->correct_answer){
                $correct_answers++;
                $tonight_score = $tonight_score + $result->question->question_time->score;
            }
        }
        $total_score = Score::where('user_id', $user_id)->first()->score;
        $total_answered_questions = TonightQuestion::where('user_id', $user_id)->where('used', '1')->whereDate('updated_at', Carbon::today())->count();
        $rank = Score::where('user_id', $user_id)->first()->getRanking();
        return response()->json([
            'correct_answers' => $correct_answers,
            'tonight_score' => $tonight_score,
            'total_score' => $total_score,
            'rank' => $rank,
            'answered_questions' => $total_answered_questions
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

    public function refresh()
    {
        $user_id = auth('api')->user()->id;

        $v1 = UsedQuestion::where('user_id', $user_id)->delete();
        $v2 = TonightQuestion::where('user_id', $user_id)->delete();
        $v3 = Score::where('user_id', $user_id)->delete();
        $v3 = Result::where('user_id', $user_id)->delete();
        return response()->json([
            'status' => '200',
            'message' => 'Done.'
        ]);
    }
}