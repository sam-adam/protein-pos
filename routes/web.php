<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'DashboardController@index');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout');
Route::resource('/users', 'UsersController');
Route::resource('/branches', 'BranchesController');
Route::post('/branches/{branchId}/license', 'BranchesController@license')->name('branches.license');
Route::post('/branches/{branchId}/activate', 'BranchesController@activate')->name('branches.activate');