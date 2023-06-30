<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SwInfoBroadcastController;
use App\Http\Controllers\BandsController;


// home
Route::get('/', function () {
    return Inertia::render('Home', [
        'user' => auth()->user()
    ]);
});

// logs
Route::get('/logs', [LogController::class, 'index'])->middleware(['auth', 'verified'])->name('logs');
Route::post('/logs', [LogController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('/logs/update/{log_id}', [LogController::class, 'update'])->middleware(['auth', 'verified']);
Route::post('/logs/filter', [LogController::class, 'filter'])->middleware(['auth', 'verified']);
Route::post('/logs/delete/{log_id}', [LogController::class, 'destroy'])->middleware(['auth', 'verified']);
// shortWaveInfoData
Route::get('/shortWaveInfoData', [SwInfoBroadcastController::class, 'index']);
Route::post('/shortWaveInfoData', [SwInfoBroadcastController::class, 'filterBroadcasts'])->name('swiDataRip');
Route::post('/swiDataRip/checkfrequency', [SwInfoBroadcastController::class, 'checkFrequency'])->middleware(['auth', 'verified']);
Route::get('/swiDataRip/rip', [SwInfoBroadcastController::class, 'rip']);
// bands
Route::post('/bands/changeBandZoom', [BandsController::class, 'changeBandZoom']);
Route::get('/bands', [BandsController::class, 'index'])->name('bands');
Route::post('/bands/getBand/{frequency}', [BandsController::class, 'getBand']);

// login / logout
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->middleware('guest')->name('login');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::get('/logout', function () {
    return Inertia::render('Auth/Logout', [
        'user' => auth()->user()
    ]);
})->middleware('auth')->name('logout');
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth');

// auth
Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->middleware('guest')->name('register');
Route::post('/register', [UserController::class, 'store'])->middleware('guest');
Route::get('/edit_profile', function () {
    return Inertia::render('Auth/EditProfile', [
        'user' => auth()->user()
    ]);
})->middleware('auth')->name('edit_profile');
Route::put('/update_profile', [UserController::class, 'update'])->middleware('auth');
Route::delete('/delete_account', [UserController::class, 'destroy'])->middleware('auth');



// TO DO :

// proper routes using put or delete where necessary, and duplicate on my breeze clone

/*
- sort out home page
- optimize queries to only select the fields needed

Bands page, 
- make it so you can adjust start and end frequencies

Swinfo page
- make the time displayed above the chart show live time
- don't show top bar of graph if no broadcasts are found
- try to fix the live time

swinfo scraper
- delete orphaned transmitters, languages and stations after a scrape

logs
- edit log
- order by filter
- try to fix the live time
- pagination

*/