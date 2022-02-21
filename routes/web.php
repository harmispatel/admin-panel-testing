<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Web\HomeController;
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

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::group(['prefix' => 'admin'], function () {

    Route::group(['namespace' => 'Admin'], function () {
        
        Route::group(['middleware' => 'admin'], function () {
            
            Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        });

    });
});


Route::group(['namespace' => 'Web'], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
