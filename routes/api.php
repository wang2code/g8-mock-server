<?php

use App\Http\Middleware\GetMockUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get_paths_data', [ApiController::class, "getMockPathData"]);
Route::get('/get_samplenames_data', [ApiController::class, "getMockSampleNamesData"]);
Route::get('/get_sample_data', [ApiController::class, "getMockSampleData"]);
Route::post('/UpdateUserFakeData', [ApiController::class, "UpdateUserFakeData"])
        ->middleware([GetMockUser::class]);
Route::get('/GetUserFakeData', [ApiController::class, "GetUserFakeData"])
        ->middleware([GetMockUser::class]);
        
