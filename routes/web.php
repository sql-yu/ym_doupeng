<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/','\App\Web\IndexController@index');
Route::get('/'.env('APP_GAME_PAGE').'/{uuid_code}','\App\Web\IndexController@game');
Route::get('/'.env('APP_DETAIL_PAGE').'/{uuid_code}','\App\Web\IndexController@detail');
Route::get('/tag/{id}','\App\Web\IndexController@tag');
Route::get('/about_us','\App\Web\IndexController@about_us');
Route::get('/privacy','\App\Web\IndexController@privacy');
Route::get('/terms','\App\Web\IndexController@terms');
Route::get('/page/{xx}','\App\Web\IndexController@page');
