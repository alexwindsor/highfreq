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
Route::post('/logs/add', [LogController::class, 'store'])->middleware(['auth', 'verified']);
Route::put('/logs/update/{log_id}', [LogController::class, 'update'])->middleware(['auth', 'verified']);
Route::post('/logs', [LogController::class, 'filter'])->middleware(['auth', 'verified']);
Route::post('/logs/checkfrequency', [LogController::class, 'checkFrequency'])->middleware(['auth', 'verified']);
Route::get('/getStations/{station_type_id}', [LogController::class, 'getStations'])->middleware(['auth', 'verified']);
Route::get('/getLanguages/{station_type_id}', [LogController::class, 'getLanguages'])->middleware(['auth', 'verified']);
Route::post('/logs', [LogController::class, 'filter'])->middleware(['auth', 'verified']);
Route::delete('/logs/delete/{log_id}', [LogController::class, 'destroy'])->middleware(['auth', 'verified']);

// shortWaveInfoData
Route::get('/shortWaveInfoData', [SwInfoBroadcastController::class, 'index']);
Route::post('/shortWaveInfoData', [SwInfoBroadcastController::class, 'filterBroadcasts']);
Route::post('/swiDataRip/checkfrequency', [SwInfoBroadcastController::class, 'checkFrequency'])->middleware(['auth', 'verified']);
Route::post('/swiDataRip/getStationProgrammes', [SwInfoBroadcastController::class, 'getStationProgrammes'])->middleware(['auth', 'verified']);
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

/*
fix deletion of orphaned broadcast stations / station programmes on sw-info scrape
prevent logging the same frequency within 30 minutes / 30 minute block by same user
add log filter for matching with sw-info data
implement headless ui
separate routes between views and api and refactor the Controller / Model pages
colour code the logs page by broadcast / utility, to make it clear which one you are on when adding a log
don't show utility logs when adding a broadcast and vice versa


doing:
---
create crud page for updating utility stations


---
done:
update bands page with aeronautical bands and tidy up
make logs recorded by the second, not by the minute
take log orders out of filter and into their own dropdown
make better date displays, maybe dates for humans in the logs, so it is clearer
*/


