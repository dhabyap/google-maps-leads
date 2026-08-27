<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RadarController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('dashboard.auth')->group(function () {
    Route::get('/', [RadarController::class, 'index']);
    Route::get('/export', [RadarController::class, 'export']);
    Route::post('/leads/{id}/status', [RadarController::class, 'updateStatus']);
    Route::post('/leads/{id}/notes', [RadarController::class, 'updateNotes']);
    Route::delete('/leads/{id}', [RadarController::class, 'destroy']);
});
