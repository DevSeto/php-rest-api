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

//
//Route::get('/languages/console', function () {
//    return view('multylanguage.multylanguage');
//});
//
//
//
////
////Auth::routes();
////
Route::get('/countries', 'TestController@getCountries');
//Route::get('/languages/key_version/{id}', 'LanguageController@show');
//Route::post('/languages', 'LanguageController@updateDeleteOrCreate');
