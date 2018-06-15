<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

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

// Routes for Users
Route::post('/users/setVerified', 'UserController@setVerified');
Route::post('/users/resendVerificationCode', 'UserController@resendVerificationCode');
Route::post('/users/login', 'UserController@login');
Route::post('/users', 'UserController@create');
Route::post('/users/{userId}/invite', 'UserController@invite')->middleware('auth.session');
Route::post('/users/{userId}/picture', 'UserController@uploadPicture')->middleware('auth.session');
Route::get('/users/{userId}', 'UserController@get')->middleware('auth.session');
Route::put('/users/{userId}', 'UserController@update')->middleware('auth.session');
Route::delete('/users/{userId}', 'UserController@delete')->middleware('auth.session');
Route::get('/users/{userId}/session', 'UserController@getSession')->middleware('auth.session');

// Routes for Tasks
Route::get('/users/{userId}/tasks', 'TaskController@index')->middleware('auth.session');
Route::get('/users/{userId}/tasks/{id}', 'TaskController@get')->middleware('auth.session');
Route::post('/users/{userId}/tasks', 'TaskController@create')->middleware('auth.session');
Route::put('/users/{userId}/tasks/{id}', 'TaskController@update')->middleware('auth.session');
Route::delete('/users/{userId}/tasks/{id}', 'TaskController@delete')->middleware('auth.session');
Route::get('/users/{userId}/tasks/report', 'TaskController@report')->middleware('auth.session');



// TODO - use this to enable authentication via OAuth2!
//
//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
