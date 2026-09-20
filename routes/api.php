<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CraftsmanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StatisticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('admin')->group(function (): void {
    Route::get('stats', DashboardController::class);
    Route::get('statistics', StatisticsController::class);
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);
    Route::put('password', [PasswordController::class, 'update']);

    Route::apiResource('services', ServiceController::class)->names('admin.services');
    Route::post('services/{service}', [ServiceController::class, 'update']);

    Route::apiResource('projects', ProjectController::class)->names('admin.projects');
    Route::post('projects/{project}', [ProjectController::class, 'update']);

    Route::get('leads', [LeadController::class, 'index']);
    Route::post('leads', [LeadController::class, 'store']);
    Route::get('leads/export', [LeadController::class, 'export']);
    Route::patch('leads/{lead}', [LeadController::class, 'update']);
    Route::delete('leads/{lead}', [LeadController::class, 'destroy']);

    Route::get('craftsmen', [CraftsmanController::class, 'index']);
    Route::get('craftsmen/export', [CraftsmanController::class, 'export']);
    Route::patch('craftsmen/{craftsman}', [CraftsmanController::class, 'update']);
    Route::delete('craftsmen/{craftsman}', [CraftsmanController::class, 'destroy']);
});
