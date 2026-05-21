<?php

use App\Livewire\CriteriaDocuments;
use App\Livewire\NotificationList;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/notifications', NotificationList::class)->name('notifications');
    Route::get('/criteria-documents', CriteriaDocuments::class)->name('criteria-documents');
});

require __DIR__.'/settings.php';
