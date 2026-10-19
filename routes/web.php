<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BikeController as AdminBikeController;
use App\Http\Controllers\Admin\StationController as AdminStationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/stations', [StationController::class, 'index'])->name('stations');

/*
|--------------------------------------------------------------------------
| JSON endpoints used by AJAX (no page reload)
|--------------------------------------------------------------------------
*/
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/stations', [StationController::class, 'list'])->name('stations.list');
    Route::get('/stations/{station}', [StationController::class, 'show'])->name('stations.show');
    Route::get('/stations/{station}/reviews', [StationController::class, 'reviews'])->name('stations.reviews');
    Route::get('/wards/{ward}/stations', [StationController::class, 'options'])->name('wards.stations');
});

/*
|--------------------------------------------------------------------------
| Register / login / logout
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// The exam asks for a plain "Đăng xuất" link (/logout), so GET is allowed as well
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Logged-in users
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/bike-rental', [RentalController::class, 'rentForm'])->name('rental.rent');
    Route::post('/bike-rental', [RentalController::class, 'rent'])->name('rental.rent.store');

    Route::get('/bike-return', [RentalController::class, 'returnForm'])->name('rental.return');
    Route::post('/bike-return', [RentalController::class, 'returnBike'])->name('rental.return.store');

    Route::get('/rental-history', [RentalController::class, 'history'])->name('rental.history');

    Route::get('/rentals/{rental}/review', [ReviewController::class, 'create'])->name('rental.review');
    Route::post('/rentals/{rental}/review', [ReviewController::class, 'store'])->name('rental.review.store');
});

/*
|--------------------------------------------------------------------------
| Admin area (/admin/*)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminAuthController::class, 'dashboard'])->name('dashboard');

        // ----- Bikes -----
        Route::prefix('bikes')->name('bikes.')->group(function () {
            Route::get('/', [AdminBikeController::class, 'index'])->name('index');
            Route::get('/create', [AdminBikeController::class, 'create'])->name('create');
            Route::post('/', [AdminBikeController::class, 'store'])->name('store');

            // Trash (must be declared before the {bicycle} routes)
            Route::get('/bin', [AdminBikeController::class, 'bin'])->name('bin');
            Route::post('/restore-selected', [AdminBikeController::class, 'restoreSelected'])->name('restore-selected');
            Route::post('/delete-selected', [AdminBikeController::class, 'deleteSelected'])->name('delete-selected');
            Route::post('/restore-all', [AdminBikeController::class, 'restoreAll'])->name('restore-all');
            Route::post('/delete-all', [AdminBikeController::class, 'deleteAll'])->name('delete-all');
            Route::post('/{id}/restore', [AdminBikeController::class, 'restore'])->whereNumber('id')->name('restore');
            Route::post('/{id}/force-delete', [AdminBikeController::class, 'forceDelete'])->whereNumber('id')->name('force-delete');

            Route::get('/{bicycle}', [AdminBikeController::class, 'show'])->whereNumber('bicycle')->name('show');
            Route::get('/{bicycle}/edit', [AdminBikeController::class, 'edit'])->whereNumber('bicycle')->name('edit');
            Route::put('/{bicycle}', [AdminBikeController::class, 'update'])->whereNumber('bicycle')->name('update');
            Route::delete('/{bicycle}', [AdminBikeController::class, 'destroy'])->whereNumber('bicycle')->name('destroy');
        });

        // ----- Stations -----
        Route::prefix('stations')->name('stations.')->group(function () {
            Route::get('/', [AdminStationController::class, 'index'])->name('index');
            Route::get('/create', [AdminStationController::class, 'create'])->name('create');
            Route::post('/', [AdminStationController::class, 'store'])->name('store');

            // Trash (must be declared before the {station} routes)
            Route::get('/bin', [AdminStationController::class, 'bin'])->name('bin');
            Route::post('/restore-selected', [AdminStationController::class, 'restoreSelected'])->name('restore-selected');
            Route::post('/delete-selected', [AdminStationController::class, 'deleteSelected'])->name('delete-selected');
            Route::post('/restore-all', [AdminStationController::class, 'restoreAll'])->name('restore-all');
            Route::post('/delete-all', [AdminStationController::class, 'deleteAll'])->name('delete-all');
            Route::post('/{id}/restore', [AdminStationController::class, 'restore'])->whereNumber('id')->name('restore');
            Route::post('/{id}/force-delete', [AdminStationController::class, 'forceDelete'])->whereNumber('id')->name('force-delete');

            Route::get('/{station}', [AdminStationController::class, 'show'])->whereNumber('station')->name('show');
            Route::get('/{station}/edit', [AdminStationController::class, 'edit'])->whereNumber('station')->name('edit');
            Route::put('/{station}', [AdminStationController::class, 'update'])->whereNumber('station')->name('update');
            Route::delete('/{station}', [AdminStationController::class, 'destroy'])->whereNumber('station')->name('destroy');
        });
    });
});
