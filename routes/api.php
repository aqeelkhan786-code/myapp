<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Webhook endpoint for immediate iCal sync
Route::post('/webhook/ical-sync', [\App\Http\Controllers\Api\WebhookController::class, 'syncIcal'])
    ->middleware('throttle:60,1'); // Rate limit: 60 requests per minute
