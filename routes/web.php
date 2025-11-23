<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

// Dashboard (protected route)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Guest Booking Routes
Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
Route::get('/booking/{room}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{room}', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/{booking}/step/{step}', [BookingController::class, 'step'])->name('booking.step');
Route::post('/booking/{booking}/step/{step}', [BookingController::class, 'saveStep'])->name('booking.save-step');
Route::post('/booking/{booking}/signature', [BookingController::class, 'saveSignature'])->name('booking.signature');
Route::post('/booking/{booking}/payment', [BookingController::class, 'processPayment'])->name('booking.payment');
Route::get('/booking/{booking}/complete', [BookingController::class, 'complete'])->name('booking.complete');

// iCal Export
Route::get('/ical/{room}/{token}.ics', [BookingController::class, 'icalExport'])->name('ical.export');
