<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CompetitionRegistrationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/google', [AuthController::class, 'googleLogin']);
    Route::post('/admin/login/google', [AuthController::class, 'loginAdmin']);
});

Route::middleware('auth:user,admin')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{key}', [EventController::class, 'show']);

Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/{key}', [CompetitionController::class, 'show']);

Route::middleware('auth:user')->group(function () {
    Route::get('/registrations', [UserController::class, 'getRegistrations']);
    Route::prefix('competitions')->group(function() {
        Route::post('/{key}/submit', [CompetitionRegistrationController::class, 'submitFinal']);
        Route::get('/{key}/status', [CompetitionRegistrationController::class, 'checkStatus']);
        Route::post('/{key}/draft', [CompetitionRegistrationController::class, 'saveDraft']);
    });

    Route::prefix('events')->group(function() {
        Route::post('/{key}/submit', [EventRegistrationController::class, 'submitFinal']);
        Route::get('/{key}/status', [EventRegistrationController::class, 'checkStatus']);
        Route::post('/{key}/draft', [EventRegistrationController::class, 'saveDraft']);
    });
});

Route::middleware('auth:admin')->group(function() {
    Route::prefix('admin')->group(function() {
        Route::middleware(['permission:manage_users'])->group(function() {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
        });

        Route::middleware(['permission:manage_registrations'])->group(function() {
            Route::get('/registrations/competitions', [CompetitionRegistrationController::class, 'index']);
            Route::patch('/registrations/competitions/{id}/status', [CompetitionRegistrationController::class, 'updateStatus']);
            Route::get('/registrations/events', [EventRegistrationController::class, 'index']);
            Route::patch('/registrations/events/{id}/status', [EventRegistrationController::class, 'updateStatus']);
        });

        Route::middleware(['permission:manage_events'])->prefix('events')->group(function() {
            Route::post('/', [EventController::class, 'store']);
            Route::put('/{key}', [EventController::class, 'update']);  
            Route::delete('/{key}', [EventController::class, 'destroy']);
        });

        Route::middleware(['permission:manage_competitions'])->prefix('competitions')->group(function() {
            Route::post('/', [CompetitionController::class, 'store']);
            Route::put('/{key}', [CompetitionController::class, 'update']);
            Route::delete('/{key}', [CompetitionController::class, 'destroy']);
        });
    });
});