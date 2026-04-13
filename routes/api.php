<?php

use App\Http\Controllers\MapDataController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\AdminSyncController;
use App\Http\Controllers\AdminClientUploadController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminAccessRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('web')->group(function () {
    Route::post('/upload/sftp-project', [ProjectController::class, 'storeSftp']);
    Route::post('/upload/google-drive-project', [ProjectController::class, 'storeGoogleDrive']);
    Route::post('/upload/init', [\App\Http\Controllers\UploadController::class, 'init']);
    Route::post('/upload/chunk', [\App\Http\Controllers\UploadController::class, 'chunk']);
    Route::post('/upload/finalize', [\App\Http\Controllers\UploadController::class, 'finalize']);

    Route::prefix('auth')->group(function () {
        Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me']);
        Route::get('/profile', [\App\Http\Controllers\AuthController::class, 'profile']);
        Route::put('/profile/name', [\App\Http\Controllers\AuthController::class, 'updateName']);
        Route::put('/profile/contact', [\App\Http\Controllers\AuthController::class, 'updateContact']);
        Route::put('/profile/password', [\App\Http\Controllers\AuthController::class, 'updatePassword']);
        Route::get('/profile/sftp', [\App\Http\Controllers\AuthController::class, 'sftp']);
        Route::put('/profile/sftp-password', [\App\Http\Controllers\AuthController::class, 'updateSftpPassword']);
    });

    Route::get('/user/my-uploads', [ProjectController::class, 'index']);
    Route::post('/user/my-uploads/{id}/confirm-received', [ProjectController::class, 'confirmReceived']);
    Route::delete('/user/my-uploads/{id}', [ProjectController::class, 'destroy']);

    Route::get('/map-data', [MapDataController::class, 'index']);
    Route::post('/map-data', [MapDataController::class, 'store']);
    Route::get('/map-data/{id}', [MapDataController::class, 'show']);
    Route::delete('/map-data/{id}', [MapDataController::class, 'destroy']);
    
    // Showcase routes
    Route::get('/showcase', [ShowcaseController::class, 'index']);
    Route::post('/showcase', [ShowcaseController::class, 'store']);
    Route::put('/showcase/{id}', [ShowcaseController::class, 'update']);
    Route::delete('/showcase/{id}', [ShowcaseController::class, 'destroy']);

    // Admin Sync and Tool routes
    Route::post('/admin/seed-mapdata-from-locations', [AdminSyncController::class, 'seedMapDataFromLocations']);
    Route::post('/admin/seed-showcase-from-locations', [AdminSyncController::class, 'seedShowcaseFromLocations']);
    Route::post('/admin/showcase-renumber', [AdminSyncController::class, 'showcaseRenumber']);
    Route::post('/admin/export-locations-json', [AdminSyncController::class, 'exportLocationsJson']);

    // Admin Client Uploads Routes
    Route::get('/admin/client-uploads', [AdminClientUploadController::class, 'getUploads']);
    Route::get('/admin/processing-requests', [AdminClientUploadController::class, 'getProcessingRequests']);
    Route::get('/admin/client-uploads/path-config', [AdminClientUploadController::class, 'getPathConfig']);
    Route::post('/admin/client-uploads/{id}/decision', [AdminClientUploadController::class, 'submitDecision']);
    Route::delete('/admin/client-uploads/{id}', [AdminClientUploadController::class, 'deleteUpload']);
    Route::post('/admin/processing-requests/{id}/delivery', [AdminClientUploadController::class, 'markDelivered']);

    // Admin Users Routes
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::post('/admin/users/promote', [AdminUserController::class, 'promote']);
    Route::post('/admin/users/upgrade-trusted', [AdminUserController::class, 'upgradeTrusted']);
    Route::post('/admin/users/downgrade-registered', [AdminUserController::class, 'downgradeRegistered']);
    Route::post('/admin/users/remove', [AdminUserController::class, 'remove']);

    // Admin Access Requests (Waitlist) Routes
    Route::get('/admin/access-requests', [AdminAccessRequestController::class, 'index']);
    Route::post('/admin/access-requests/{id}/approve', [AdminAccessRequestController::class, 'approve']);
    Route::post('/admin/access-requests/{id}/reject', [AdminAccessRequestController::class, 'reject']);
});
