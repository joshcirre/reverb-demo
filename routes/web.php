<?php

use App\Events\SwitchFlipped;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

Route::get('/broadcast-test', function () {
    broadcast(new SwitchFlipped(true))->toOthers();

    return 'Event broadcasted!';
});
require __DIR__.'/auth.php';
