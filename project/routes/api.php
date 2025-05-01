<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UtilisateurController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/utilisateur/ping', [UtilisateurController::class, 'ping']);
Route::get('/material/ping', [MaterialController::class, 'ping']);
Route::post('/utilisateur', [UtilisateurController::class, 'store']);

Route::get('/config/jwt', function () {
    return response()->json([
        'jwt_enabled' => env('JWT_ENABLE', false)
    ]);
});

Route::middleware(env('JWT_ENABLE', false) ? 'jwt' : [])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('client', ClientController::class);
    Route::apiResource('material', MaterialController::class);
    Route::apiResource('sale', SaleController::class);
    Route::apiResource('ticket', TicketController::class);
	Route::apiResource('utilisateur', UtilisateurController::class)->except(['store']);
});
