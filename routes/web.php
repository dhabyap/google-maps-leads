<?php

use App\Http\Controllers\RadarController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RadarController::class, 'index']);
Route::post('/leads/{id}/status', [RadarController::class, 'updateStatus']);
Route::post('/leads/{id}/notes', [RadarController::class, 'updateNotes']);
