<?php

use App\Livewire\NotificationList;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/notifications', NotificationList::class)->name('notifications');
});

require __DIR__.'/settings.php';
