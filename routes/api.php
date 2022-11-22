<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api/auth',
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('user-profile', 'AuthController@me');
});
Route::group([
    'prefix' => 'api',
], function ($router) {
    Route::get('/order','OrderController@index');
    Route::post('/order','OrderController@store');
});
