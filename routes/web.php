<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});


Route::group(['prefix' => '528604B1F7F0DCC6D6C5C1001E53C644E447C50442782FCA9AE65ABC6A116272'], function () {
    Voyager::routes();
});

Route::post('/upload', function (Request $request) {
    $path = $request->file('avatar')->store('/public/avatars');
    return $path;
});