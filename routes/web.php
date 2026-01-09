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
    return redirect()->route('booking-flow.home');
});

require __DIR__.'/auth.php';

// Dashboard (protected route)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/check-in-pdf/{pdf}/download', [\App\Http\Controllers\DashboardController::class, 'downloadCheckInPdf'])->name('dashboard.checkin-pdf.download');
    Route::post('/dashboard/check-in-pdf/send', [\App\Http\Controllers\DashboardController::class, 'sendCheckInPdfs'])->name('dashboard.checkin-pdf.send');
    
    // Profile Routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin Routes (require admin role)
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('bookings', \App\Http\Controllers\Admin\BookingController::class);
        Route::get('bookings/calendar/view', [\App\Http\Controllers\Admin\BookingController::class, 'calendar'])->name('bookings.calendar');
        Route::get('bookings/calendar/table', [\App\Http\Controllers\Admin\BookingController::class, 'calendarTable'])->name('bookings.calendar-table');
        Route::post('bookings/{booking}/documents/{document}/regenerate', [\App\Http\Controllers\Admin\BookingController::class, 'regenerateDocument'])->name('bookings.documents.regenerate');
        Route::post('bookings/{booking}/send-documents', [\App\Http\Controllers\Admin\BookingController::class, 'sendDocuments'])->name('bookings.send-documents');
        Route::post('bookings/{booking}/mark-paid', [\App\Http\Controllers\Admin\BookingController::class, 'markAsPaid'])->name('bookings.mark-paid');
        Route::resource('properties', \App\Http\Controllers\Admin\PropertyController::class);
        Route::resource('locations', \App\Http\Controllers\Admin\LocationController::class);
        Route::resource('houses', \App\Http\Controllers\Admin\HouseController::class);
        Route::post('houses/{house}/images', [\App\Http\Controllers\Admin\HouseController::class, 'uploadImage'])->name('houses.images.upload');
        Route::put('houses/{house}/images/order', [\App\Http\Controllers\Admin\HouseController::class, 'updateImageOrder'])->name('houses.images.order');
        Route::delete('houses/{house}/images/{image}', [\App\Http\Controllers\Admin\HouseController::class, 'deleteImage'])->name('houses.images.delete');
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
        Route::post('settings/landlord', [\App\Http\Controllers\Admin\SettingsController::class, 'updateLandlord'])->name('settings.landlord');
        Route::post('settings/email', [\App\Http\Controllers\Admin\SettingsController::class, 'updateEmailTemplate'])->name('settings.email');
    });
});

// Language switching route (supports both GET and POST)
Route::match(['get', 'post'], '/set-locale', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'locale' => 'required|string|in:en,de',
    ]);
    
    // Mark that user has explicitly set a language preference
    session(['locale' => $request->locale]);
    session(['locale_user_set' => true]);
    app()->setLocale($request->locale);
    
    // Redirect to specified page or back
    if ($request->has('redirect_to')) {
        return redirect($request->redirect_to);
    }
    
    return redirect()->back();
})->name('set-locale');

// Booking Flow Routes (New Flow: Home → Locations → House → Search → Booking Form)
Route::prefix('booking-flow')->name('booking-flow.')->group(function () {
    Route::get('/home', [\App\Http\Controllers\Customer\BookingFlowController::class, 'home'])->name('home');
    Route::get('/locations', [\App\Http\Controllers\Customer\BookingFlowController::class, 'locations'])->name('locations');
    Route::get('/locations/{location}/house', [\App\Http\Controllers\Customer\BookingFlowController::class, 'house'])->name('house');
    Route::get('/locations/{location}/house/{house}/search', [\App\Http\Controllers\Customer\BookingFlowController::class, 'search'])->name('search');
    // Redirect old apartments route to house page
    Route::get('/houses/{house}/apartments', [\App\Http\Controllers\Customer\BookingFlowController::class, 'apartments'])->name('apartments');
    Route::get('/rooms/{room}/details', [\App\Http\Controllers\Customer\BookingFlowController::class, 'roomDetails'])->name('room-details');
});

// Guest Booking Routes
Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
Route::get('/booking/{room}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking/{room}', [BookingController::class, 'store'])->name('booking.store');

// 3-Step Booking Form (Before booking creation)
Route::get('/booking/{room}/form', [BookingController::class, 'showForm'])->name('booking.form');
Route::post('/booking/{room}/form/step/{step}', [BookingController::class, 'saveFormStep'])->name('booking.form-step');
Route::post('/booking/{room}/form/complete', [BookingController::class, 'completeForm'])->name('booking.form-complete');

Route::get('/booking/{booking}/step/{step}', [BookingController::class, 'step'])->name('booking.step');
Route::post('/booking/{booking}/step/{step}', [BookingController::class, 'saveStep'])->name('booking.save-step');
Route::post('/booking/{booking}/signature', [BookingController::class, 'saveSignature'])->name('booking.signature');
Route::post('/booking/{booking}/payment', [BookingController::class, 'processPayment'])->name('booking.payment');
Route::post('/booking/payment-intent', [BookingController::class, 'createPaymentIntent'])->name('booking.payment-intent');
Route::get('/booking/{booking}/complete', [BookingController::class, 'complete'])->name('booking.complete');

// Booking Lookup (for customers to view their bookings)
Route::get('/booking/lookup', [BookingController::class, 'lookup'])->name('booking.lookup');
Route::post('/booking/find', [BookingController::class, 'findBookings'])->name('booking.find');
Route::get('/booking/{booking}/view', [BookingController::class, 'view'])->name('booking.view');

// iCal Export
Route::get('/ical/{room}/{token}.ics', [BookingController::class, 'icalExport'])->name('ical.export');

// Document Download
Route::get('/documents/{documentId}/download', [BookingController::class, 'downloadDocument'])->name('documents.download');

// Storage Image Route (fallback if symlink doesn't work)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $file = file_get_contents($filePath);
    $type = mime_content_type($filePath);
    
    return response($file, 200)->header('Content-Type', $type);
})->where('path', '.*')->name('storage.serve');
