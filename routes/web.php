<?php

use App\Http\Controllers\DevController;
use App\Http\Middleware\GetMockUser;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dev/mock', [DevController::class, 'mock'])
        ->middleware([GetMockUser::class]);
