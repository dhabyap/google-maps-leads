<?php

use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

// Scraper ingestion endpoint — protected by API key
Route::middleware('api.key')->group(function () {
    Route::post('/leads/upsert', [LeadController::class, 'upsert']);
    Route::get('/leads', [LeadController::class, 'index']);
});
