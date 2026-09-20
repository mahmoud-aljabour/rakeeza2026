<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CraftsmanController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['ar', 'en'])
    ->name('locale.switch');

Route::get('/', LandingController::class)->name('landing');
Route::get('/services/{service}', ServiceController::class)->name('services.show');
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:quote-requests')
    ->name('leads.store');

Route::get('/craftsman', [CraftsmanController::class, 'create'])->name('craftsman.create');
Route::post('/craftsman', [CraftsmanController::class, 'store'])
    ->middleware('throttle:quote-requests')
    ->name('craftsman.store');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:admin-login')
    ->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*')
    ->name('admin');
