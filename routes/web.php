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
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class);
        Route::get('bookings/calendar/view', [\App\Http\Controllers\Admin\BookingController::class, 'calendar'])->name('bookings.calendar');
        Route::post('bookings/{booking}/documents/{document}/regenerate', [\App\Http\Controllers\Admin\BookingController::class, 'regenerateDocument'])->name('bookings.documents.regenerate');
        Route::post('bookings/{booking}/mark-paid', [\App\Http\Controllers\Admin\BookingController::class, 'markAsPaid'])->name('bookings.mark-paid');
        Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);
        Route::post('ical/sync', [\App\Http\Controllers\Admin\IcalController::class, 'sync'])
            ->middleware('throttle:ical-sync')
            ->name('ical.sync');
        Route::post('rooms/{room}/ical/import', [\App\Http\Controllers\Admin\RoomController::class, 'updateIcalImport'])->name('rooms.ical.import');
        Route::post('rooms/{room}/ical/export', [\App\Http\Controllers\Admin\RoomController::class, 'manageExportToken'])->name('rooms.ical.export');
        Route::post('rooms/{room}/ical/sync', [\App\Http\Controllers\Admin\RoomController::class, 'syncIcal'])
            ->middleware('throttle:ical-sync')
            ->name('rooms.ical.sync');
        Route::post('rooms/{room}/images', [\App\Http\Controllers\Admin\RoomController::class, 'uploadImage'])->name('rooms.images.upload');
        Route::put('rooms/{room}/images/order', [\App\Http\Controllers\Admin\RoomController::class, 'updateImageOrder'])->name('rooms.images.order');
        Route::delete('rooms/{room}/images/{image}', [\App\Http\Controllers\Admin\RoomController::class, 'deleteImage'])->name('rooms.images.delete');
        Route::post('rooms/{room}/blackout-dates', [\App\Http\Controllers\Admin\RoomController::class, 'storeBlackoutDate'])->name('rooms.blackout-dates.store');
        Route::delete('rooms/{room}/blackout-dates/{blackoutDate}', [\App\Http\Controllers\Admin\RoomController::class, 'deleteBlackoutDate'])->name('rooms.blackout-dates.delete');
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings/general', [\App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('settings.general');
        Route::post('settings/payment', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePayment'])->name('settings.payment');
        Route::post('settings/email', [\App\Http\Controllers\Admin\SettingsController::class, 'updateEmailTemplate'])->name('settings.email');
    });
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

// Document Download
Route::get('/documents/{documentId}/download', [BookingController::class, 'downloadDocument'])->name('documents.download');
