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
Route::post('/products/{product}/remove-inventory', 'ProductsController@removeInventory')->name('products.inventory.remove');
//Route::resource('/inventory-movements', 'InventoryMovementsController');
Route::resource('/product-variants', 'ProductVariantsController');
Route::resource('/packages', 'PackagesController');
Route::resource('/customer-groups', 'CustomerGroupsController');
Route::resource('/customers', 'CustomersController');
Route::post('/customers/bulk-change-group', 'CustomersController@bulkChangeGroup')->name('customers.bulk_change_group');
Route::post('/customers/bulk-delete', 'CustomersController@bulkDelete')->name('customers.bulk_delete');
Route::get('/shifts/in', 'ShiftsController@viewClockIn')->name('shifts.viewIn');
Route::post('/shifts/in', 'ShiftsController@clockIn')->name('shifts.in');
Route::get('/shifts/out', 'ShiftsController@viewClockOut')->name('shifts.viewOut');
Route::post('/shifts/{shift}/out', 'ShiftsController@clockOut')->name('shifts.out');
Route::resource('/shifts', 'ShiftsController');
Route::resource('/sales', 'SalesController');
Route::get('/settings', 'SettingsController@index')->name('settings.index');
Route::post('/settings', 'SettingsController@update')->name('settings.update');