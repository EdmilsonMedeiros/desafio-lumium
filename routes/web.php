<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LogDNSController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('log-dns')->group(function () {
        Route::post('/', [LogDNSController::class, 'submit'])->name('log-dns.submit');
        Route::post('/get-log-dns', [LogDNSController::class, 'getLogDNSs'])->name('log-dns.get-log-dns');
        Route::get('/statistics', [LogDNSController::class, 'getLogDNSStatistics'])->name('log-dns.statistics');
    });
});
