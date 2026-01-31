<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\EventController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/google', [AuthController::class, 'googleLogin']);
});

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'loginAdmin']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{key}', [EventController::class, 'show']);

Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{key}', [CompetitionController::class, 'show']);
