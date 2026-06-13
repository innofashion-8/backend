<?php

use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CompetitionRegistrationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EvaluationQuestionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Route::post('/register', [AuthController::class, 'register']);
    // Route::post('/login', [AuthController::class, 'login']);
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
    Route::post('/profile/update', [UserController::class, 'updateProfile']);
    Route::get('/complete-registration/status', [UserController::class, 'checkStatus']);
    Route::post('/complete-registration/draft', [UserController::class, 'saveDraft']);
    Route::post('/complete-registration/submit', [UserController::class, 'submitRegister']);
    Route::get('/registrations', [UserController::class, 'getRegistrations']);
    Route::prefix('competitions')->group(function () {
        Route::post('/{key}/submit', [CompetitionRegistrationController::class, 'submitFinal']);
        Route::post('/{key}/submission', [CompetitionRegistrationController::class, 'uploadSubmission']);
        Route::post('/{key}/chunk-upload', [CompetitionRegistrationController::class, 'uploadChunk']);
        Route::get('/{key}/status', [CompetitionRegistrationController::class, 'checkStatus']);
        Route::post('/{key}/draft', [CompetitionRegistrationController::class, 'saveDraft']);
    });

    Route::prefix('events')->group(function () {
        Route::post('/{key}/submit', [EventRegistrationController::class, 'submitFinal']);
        Route::get('/{key}/status', [EventRegistrationController::class, 'checkStatus']);
        Route::post('/{key}/draft', [EventRegistrationController::class, 'saveDraft']);
        Route::post('/scan-attendance', [EventRegistrationController::class, 'userScanCheckIn']);
        Route::get('/{key}/evaluation-questions', [EventRegistrationController::class, 'getEvaluationQuestions']);
        Route::post('/{key}/evaluation', [EventRegistrationController::class, 'submitEvaluation']);
    });
});

Route::middleware('auth:admin')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('/impersonate', [AuthController::class, 'impersonate']);
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
        Route::middleware(['permission:manage_users'])->group(function () {
            Route::get('/users/export', [UserController::class, 'exportUsers']);
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
        });

        Route::middleware(['permission:manage_registrations'])->group(function () {
            Route::get('/registrations/competitions/export', [CompetitionRegistrationController::class, 'exportRegistrations']);
            Route::get('/registrations/competitions', [CompetitionRegistrationController::class, 'index']);
            Route::patch('/registrations/competitions/{id}/status', [CompetitionRegistrationController::class, 'updateStatus']);

            Route::get('/registrations/events/export', [EventRegistrationController::class, 'exportRegistrations']);
            Route::get('/registrations/events', [EventRegistrationController::class, 'index']);
            Route::patch('/registrations/events/{id}/status', [EventRegistrationController::class, 'updateStatus']);
            Route::patch('/registrations/events/{id}/attendance', [EventRegistrationController::class, 'updateAttendance']);
        });

        Route::middleware(['permission:manage_events'])->prefix('events')->group(function () {
            Route::post('/', [EventController::class, 'store']);
            Route::put('/{key}', [EventController::class, 'update']);
            Route::delete('/{key}', [EventController::class, 'destroy']);

            // Evaluation Questions
            Route::get('/{eventId}/evaluation-questions', [EvaluationQuestionController::class, 'index']);
            Route::post('/{eventId}/evaluation-questions', [EvaluationQuestionController::class, 'store']);
            Route::put('/{eventId}/evaluation-questions/{id}', [EvaluationQuestionController::class, 'update']);
            Route::delete('/{eventId}/evaluation-questions/{id}', [EvaluationQuestionController::class, 'destroy']);
            Route::patch('/{eventId}/evaluation-questions/reorder', [EvaluationQuestionController::class, 'reorder']);
            Route::post('/{eventId}/evaluation-questions/import', [EvaluationQuestionController::class, 'importQuestions']);
            Route::get('/{eventId}/evaluation-responses', [EvaluationQuestionController::class, 'responses']);
        });

        Route::middleware(['permission:manage_competitions'])->prefix('competitions')->group(function () {
            Route::post('/', [CompetitionController::class, 'store']);
            Route::put('/{key}', [CompetitionController::class, 'update']);
            Route::delete('/{key}', [CompetitionController::class, 'destroy']);
        });

        Route::middleware(['permission:scan_attendance'])->prefix('scan')->group(function () {
            Route::post('/attendance', [EventRegistrationController::class, 'checkIn']);
        });
        Route::get('/events/{key}/rotating-qr', [EventController::class, 'getRotatingQr']);

        Route::middleware(['permission:manage_divisions'])->prefix('divisions')->group(function () {
            Route::get('/', [DivisionController::class, 'index']);
            Route::get('/{id}', [DivisionController::class, 'show']);
            Route::post('/', [DivisionController::class, 'store']);
            Route::put('/{id}', [DivisionController::class, 'update']);
            Route::delete('/{id}', [DivisionController::class, 'destroy']);
        });

        Route::middleware(['permission:manage_admins'])->prefix('admins')->group(function () {
            Route::get('/', [AdminManagementController::class, 'index']);
            Route::get('/{id}', [AdminManagementController::class, 'show']);
            Route::post('/', [AdminManagementController::class, 'store']);
            Route::put('/{id}', [AdminManagementController::class, 'update']);
            Route::delete('/{id}', [AdminManagementController::class, 'destroy']);
        });

        Route::middleware(['permission:manage_admins'])->prefix('roles-permissions')->group(function () {
            // Roles
            Route::get('/roles', [RolePermissionController::class, 'indexRoles']);
            Route::get('/roles/{id}', [RolePermissionController::class, 'showRole']);
            Route::post('/roles', [RolePermissionController::class, 'storeRole']);
            Route::put('/roles/{id}', [RolePermissionController::class, 'updateRole']);
            Route::delete('/roles/{id}', [RolePermissionController::class, 'destroyRole']);
            Route::post('/roles/{roleId}/permissions', [RolePermissionController::class, 'assignPermissions']);

            // Permissions
            Route::get('/permissions', [RolePermissionController::class, 'indexPermissions']);
            Route::get('/permissions/{id}', [RolePermissionController::class, 'showPermission']);
            Route::post('/permissions', [RolePermissionController::class, 'storePermission']);
            Route::put('/permissions/{id}', [RolePermissionController::class, 'updatePermission']);
            Route::delete('/permissions/{id}', [RolePermissionController::class, 'destroyPermission']);
        });
    });
});
