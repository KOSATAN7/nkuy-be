<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\SportsController;
use App\Http\Controllers\PertandinganController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuController;
use App\Http\Middleware\CheckAdminVenue;
use App\Http\Middleware\CheckSuperAdmin;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Autentikasi
Route::post('/register-superadmin', [AuthController::class, 'daftarSuperAdmin']);
Route::post('/register-infobar', [AuthController::class, 'daftarInfobar']);

Route::middleware([EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::post('/login', [AuthController::class, 'masuk']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/check-login', [AuthController::class, 'cekMasuk']);
    Route::post('/logout', [AuthController::class, 'keluar']);
});

// Superadmin - Kelola Venue
Route::middleware(['auth:sanctum', CheckSuperAdmin::class])
    ->prefix('venue')
    ->controller(VenueController::class)
    ->group(function () {
        Route::post('/', 'buatVenue');
        Route::get('/', 'ambilSemuaVenue');
        Route::put('/{id}', 'ubahVenue');
        Route::put('/status/{id}', 'ubahStatus');
        Route::delete('/{id}', 'hapusVenue');
    });

// Superadmin - Kelola Pertandingan
Route::middleware(['auth:sanctum', CheckSuperAdmin::class])
    ->prefix('pertandingan')
    ->controller(PertandinganController::class)
    ->group(function () {
        Route::post('/', 'buatPertandingan');
        Route::get('/', 'ambilSemuaPertandingan');
        Route::put('/{id}', 'ubahPertandingan');
        Route::put('/status/{id}', 'ubahStatus');
        Route::delete('/{id}', 'hapusPertandingan');
    });

// Superadmin - Kelola Pengguna
Route::middleware(['auth:sanctum', CheckSuperAdmin::class])
    ->prefix('user')
    ->controller(UserController::class)
    ->group(function () {
        Route::get('/', 'ambilSemuaPengguna');
        Route::get('/{id}', 'ambilPenggunaBerdasarkanId');
        Route::put('/{id}', 'ubahPengguna');
        Route::delete('/{id}', 'hapusPengguna');
    });

// Admin Venue - Kelola Menu
Route::middleware(['auth:sanctum', CheckAdminVenue::class])
    ->prefix('menu/venue/{venueId}')
    ->controller(MenuController::class)
    ->group(function () {
        Route::get('/', 'ambilMenuBerdasarkanVenue');
        Route::get('/aktif', 'menuAktifBerdasarkanVenue');
        Route::get('/{menuId}', 'ambilDetailMenu');
        Route::post('/', 'tambahMenu');
        Route::put('/{menuId}', 'ubahMenu');
        Route::delete('/{menuId}', 'hapusMenu');
    });

// Venue - Umum
Route::prefix('venue')->controller(VenueController::class)->group(function () {
    Route::get('/aktif', 'ambilSemuaVenueAktif');
    Route::get('/pertandingan/{pertandinganId}', 'ambilVenueBerdasarkanPertandingan');
    Route::get('/kota/{city}', 'ambilVenueBerdasarkanKota');
    Route::get('/{id}', 'detailVenue');
});

// Olahraga - Umum
Route::prefix('sports')->controller(SportsController::class)->group(function () {
    Route::get('/categories', 'ambilKategori');
    Route::get('/{sport}/countries', 'ambilNegaraBerdasarkanKategori');
    Route::get('/{sport}/leagues', 'ambilLigaBerdasarkanKategoriNegaraMusim');
    Route::get('/{sport}/teams', 'ambilTimBerdasarkanLiga');
    Route::post('/schedule', 'buatJadwal');
    Route::get('/{sport}/fixtures', 'ambilPertandinganBerdasarkanMusim');
});

// Pertandingan - Umum
Route::prefix('pertandingan')->controller(PertandinganController::class)->group(function () {
    Route::get('/aktif', 'ambilSemuaPertandinganAktif');
    Route::get('/{id}', 'ambilDetailPertandingan');
});

// Pengambilan File Venue
Route::get('/{filename}', function ($filename) {
    $path = public_path('storage/venues/' . $filename);
    if (!file_exists($path)) abort(404);
    return response()->file($path);
});
