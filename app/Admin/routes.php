<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->resource('/game','GameController');
    $router->resource('/gameCate','GameCateController');
    $router->resource('/reptile','GameReptileController');
    $router->get('/reptileList/{id}','GameReptileController@reptileList');

    // 排序首页修改推荐位
    $router->put('/reptileList/{id}/{id2}','GameReptileController@reptileEditOne');


    $router->get('/gamesort','GameSortController@index');
    $router->post('/gamesort/','GameSortController@store');
    $router->put('/gamesort/{id}','GameSortController@updaterecommend');

    $router->get('/gamesort/editTree/{id}','GameSortController@editTree');
    $router->post('/gamesort/editTree/{id}','GameSortController@editTreePost');

    $router->get('/gamesort/{id}/edit','GameSortController@edit');
    $router->post('/gamesort/updateJiben/','GameSortController@updateJiben');

    $router->get('/gamesort/create/','GameSortController@create');





    $router->any('users/files', 'ImageController@handle');
    $router->get('/setting','SettingController@index');
    $router->post('/setting/insert','SettingController@insert');
    $router->get('/about_us','AboutUsController@index');
    $router->post('/about_us/insert','AboutUsController@insert');
    $router->get('/privacy','PrivacyController@index');
    $router->post('/privacy/insert','PrivacyController@insert');
    $router->get('/terms','TermController@index');
    $router->post('/terms/insert','TermController@insert');





});
