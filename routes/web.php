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
Route::resource('/brands', 'BrandsController');
Route::resource('/categories', 'ProductCategoriesController');
Route::resource('/products', 'ProductsController');
Route::post('/products/{product}/add-inventory', 'ProductsController@addInventory')->name('products.inventory.add');
Route::post('/products/{product}/move-inventory', 'ProductsController@moveInventory')->name('products.inventory.move');
//Route::resource('/inventory-movements', 'InventoryMovementsController');
Route::resource('/product-variants', 'ProductVariantsController');
Route::resource('/product-sets', 'ProductSetsController');