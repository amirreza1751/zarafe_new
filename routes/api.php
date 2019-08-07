<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    Route::get('/get_video', 'API\QuestionController@get_video');
    Route::get('/get_question/{question_id}', 'API\QuestionController@get_question');
    Route::get('/send_answer', 'API\QuestionController@send_answer');
    Route::get('/results', 'API\QuestionController@results');
    Route::get('/leaderboard', 'API\ScoreController@leaderboard');
    Route::get('/refresh', 'API\QuestionController@refresh');

});

Route::get('/test', 'API\QuestionController@test');



Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'users'
], function(){
    Route::get('/view_profile', 'API\UserController@view_profile');
    Route::post('/edit_profile', 'API\UserController@edit_profile');
    Route::post('/send_invitation_code', 'API\UserController@check_invite_code');
});


Route::group([
//    'middleware' => 'auth:api',
    'prefix' => 'posts'
], function(){
    Route::get('/', 'API\PostController@index');
    Route::get('/show/{id}', 'API\PostController@show');
});