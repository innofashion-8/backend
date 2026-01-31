<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{key}', [EventController::class, 'show']);

Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{key}', [CompetitionController::class, 'show']);
