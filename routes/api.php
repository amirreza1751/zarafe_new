<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** authentication routes */
Route::group([
    'prefix' => 'auth'
], function () {
//    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::post('/send_otp','API\OtpController@send_otp');
/** end auth routes */


//Route::get('/posts', 'API\PostController@index');

Route::group([
    'middleware' => 'auth:api'
], function(){
    Route::get('/prepare_questions', 'API\QuestionController@prepare_questions');
    Route::get('/get_question', 'API\QuestionController@get_question');
    Route::get('/send_answer', 'API\QuestionController@send_answer');
    Route::get('/results', 'API\QuestionController@results');
    Route::get('/leader_board', 'API\ScoreController@leader_board');
});

Route::get('/test', 'API\QuestionController@test');
