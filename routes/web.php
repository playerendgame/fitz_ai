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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::namespace('App\Http\Controllers\Admin')->group(function() {
    Route::get('/', 'Login\LoginController@index');
    Route::get('/prompt', 'Prompt\PromptController@index');
});


Route::namespace('App\Http\Controllers\Ajax')->group(function() {

    Route::post('/prompt/login', 'Login\LoginController@login');
    Route::post('/predict', 'Prompt\PromptController@predict');
}); 