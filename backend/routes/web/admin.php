<?php

use App\Filament\Support\Impersonator;
use Illuminate\Support\Facades\Route;

// Ends a super-admin impersonation session (started from the admin panel).
Route::middleware('auth')->get('impersonate/stop', function (Impersonator $impersonator) {
    $admin = $impersonator->stop();

    return $admin
        ? redirect()->to('/admin/users')
        : redirect()->to('/login');
})->name('admin.impersonate.stop');
