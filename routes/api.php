<?php

use App\Http\Controllers\aboutController;
use App\Http\Controllers\carouselController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\detailController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\jurnalPageController;
use App\Http\Controllers\kategoriController;
use App\Http\Controllers\konfigurasiController;
use App\Http\Controllers\kontenController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\profilController;
use App\Http\Controllers\searchController;
use App\Http\Controllers\topikController;
use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Route;
Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello from Laravel API!'
    ]);
});

Route::prefix('/v1')->group(function () {
    Route::post('auth/login', [loginController::class, 'postLogin'])->name('postLogin');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout',             [loginController::class, 'logout']);

        Route::get('auth/user-index',                  [userController::class, 'index']);
        Route::get('auth/userEditPage/{id}',           [userController::class, 'userEditPage']);
        Route::post('/auth/postUser',                  [userController::class, 'postUser']);
        Route::post('/auth/destUser',                  [userController::class, 'destUser']);
        Route::post('/auth/editUser',                  [userController::class, 'editUser']);
        
        Route::get('auth/kategoris',                   [kategoriController::class, 'index']);
        Route::get('auth/kategoriEditPage/{id}',       [kategoriController::class, 'kategoriEditPage']);
        Route::post('/auth/postKategori',              [kategoriController::class, 'postKategori']);
        Route::post('/auth/destKategori',              [kategoriController::class, 'destKategori']);
        Route::post('/auth/updateKategori',            [kategoriController::class, 'updateKategori']);
        
        Route::get('auth/topik/{id_kategori}',         [topikController::class, 'index']);
        Route::get('auth/topikEditPage/{id_topik}',    [topikController::class, 'topikEditPage']);
        Route::post('/auth/postTopik',                 [topikController::class, 'postTopik']);
        Route::post('/auth/destTopik',                 [topikController::class, 'destTopik']);
        Route::post('/auth/updateTopik',               [topikController::class, 'updateTopik']);
        
        Route::get('auth/konten',                      [kontenController::class, 'index']);
        Route::get('auth/halamanPost',                 [kontenController::class, 'halamanPost']);
        Route::get('auth/kontenEditPage',              [kontenController::class, 'halamanEdit']);
        Route::post('/auth/postKonten',                [kontenController::class, 'postKonten']);
        Route::post('/auth/destKonten',                [kontenController::class, 'destKonten']);
        Route::post('/auth/updateKonten',              [kontenController::class, 'updateKonten']);
        
        Route::get('auth/konfigurasi',                 [konfigurasiController::class, 'index']);
        Route::post('/auth/updateKonfigurasi',         [konfigurasiController::class, 'updateKonfigurasi']);
        
        Route::get('auth/carousel',                    [carouselController::class, 'index']);
        Route::get('auth/carouselEditPage/{nama_foto}',[carouselController::class, 'carouselEditPage']);
        Route::post('/auth/postCarousel',              [carouselController::class, 'postCarousel']);
        Route::post('/auth/destCarousel',              [carouselController::class, 'destCarousel']);
        Route::post('/auth/updateCarousel',            [carouselController::class, 'updateCarousel']);
        
        Route::get('auth/profil',                      [profilController::class, 'index']);
        Route::get('auth/profil/historiLike',          [profilController::class, 'historiLike']);
        Route::get('auth/profil/historiKomen',         [profilController::class, 'historiKomen']);
        Route::post('/auth/destProfil',                [profilController::class, 'destProfil']);
        Route::post('/auth/updateProfil',              [profilController::class, 'updateProfil']);
        
        Route::get('/auth/dashboard',                  [dashboardController::class, 'index']);
        
        Route::get('auth/artikel/view',                [detailController::class, 'view']); 
        Route::post('auth/artikel/komen',              [detailController::class, 'komen']); 
        Route::post('auth/artikel/editKomen',          [detailController::class, 'editKomen']); 
        Route::get('auth/artikel/selectedKomen',       [detailController::class, 'selectedKomen']); 
        Route::post('auth/artikel/destKomen',          [detailController::class, 'destKomen']); 
        Route::post('auth/artikel/like',               [detailController::class, 'like']); 
        
        Route::get('auth/jurnal',                      [jurnalPageController::class, 'index']); 
        Route::post('auth/postJurnal',                 [jurnalPageController::class, 'postJurnal']); 
    });
    Route::get('auth/home',      [homeController::class, 'index']); 
    Route::get('auth/navbar',    [homeController::class, 'navbar']); 
    Route::get('auth/footer',    [homeController::class, 'footer']); 

    Route::get('auth/search',    [searchController::class, 'cari']);
    Route::get('auth/kategori',  [searchController::class, 'kategori']); 

    Route::get('auth/artikel',   [detailController::class, 'artikel']);

    Route::get('auth/about-us',  [aboutController::class, 'index']); 

    Route::post('/auth/register',[userController::class, 'register']);
});