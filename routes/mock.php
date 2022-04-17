<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\GetMockUser;

use App\Http\Controllers\MockController;

/*
|--------------------------------------------------------------------------
| Mock Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "mock" middleware group. Now create something great!
|
*/

Route::any('{any}', MockController::class)
    ->where('any', '.*');

