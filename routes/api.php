<?php

use App\Http\Controllers\MapDataController;
use App\Http\Controllers\ProjectController;
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
    Route::get('/map-data/{id}', [MapDataController::class, 'show']);
    Route::get('/showcase', [MapDataController::class, 'index']);
});
