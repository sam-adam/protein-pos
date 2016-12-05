<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'DashboardController@index');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login');
