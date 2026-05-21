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

// 🚀 Unlimited Direct Upload Hub (Stateless/No-Session for high-speed stability)
Route::post('/upload/init', [\App\Http\Controllers\UploadController::class, 'init'])->name('api.upload.init');
Route::post('/upload/direct', [\App\Http\Controllers\UploadController::class, 'direct'])->name('api.upload.direct');
// NOTE: finalize needs 'web' middleware to access auth()->user() via session

Route::middleware('web')->group(function () {
    // 🔑 Finalize needs session auth to identify the uploading user
    Route::post('/upload/finalize', [\App\Http\Controllers\UploadController::class, 'finalize'])->name('api.upload.finalize');

    Route::post('/upload/sftp-project', [ProjectController::class, 'storeSftp'])->name('api.upload.sftp_project');
    Route::post('/upload/google-drive-project', [ProjectController::class, 'storeGoogleDrive'])->name('api.upload.google_drive_project');
    Route::post('/upload/onedrive-project', [ProjectController::class, 'storeOneDrive'])->name('api.upload.onedrive_project');

    Route::prefix('auth')->group(function () {
        Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me']);
        Route::get('/profile', [\App\Http\Controllers\AuthController::class, 'profile']);
        Route::put('/profile/name', [\App\Http\Controllers\AuthController::class, 'updateName']);
        Route::put('/profile/contact', [\App\Http\Controllers\AuthController::class, 'updateContact']);
        Route::put('/profile/password', [\App\Http\Controllers\AuthController::class, 'updatePassword']);
        Route::get('/profile/sftp', [\App\Http\Controllers\AuthController::class, 'sftp']);
        Route::put('/profile/sftp-password', [\App\Http\Controllers\AuthController::class, 'updateSftpPassword']);
    });


    Route::get('/map-data', [MapDataController::class, 'index']);
    Route::post('/map-data', [MapDataController::class, 'store']);
    Route::get('/map-data/{id}', [MapDataController::class, 'show']);
    Route::delete('/map-data/{id}', [MapDataController::class, 'destroy']);
    
    // showcases routes
    Route::get('/showcases', [ShowcaseController::class, 'index']);
    Route::post('/showcases', [ShowcaseController::class, 'store']);
    Route::put('/showcases/{id}', [ShowcaseController::class, 'update']);
    Route::delete('/showcases/{id}', [ShowcaseController::class, 'destroy']);

    // Admin Sync and Tool routes
    Route::post('/admin/seed-map_data-from-locations', [AdminSyncController::class, 'seedMapDataFromLocations']);
    Route::post('/admin/seed-showcases-from-locations', [AdminSyncController::class, 'seedShowcasesFromLocations']);
    Route::post('/admin/showcases-renumber', [AdminSyncController::class, 'showcasesRenumber']);
    Route::post('/admin/export-locations-json', [AdminSyncController::class, 'exportLocationsJson']);

    // Admin Client Uploads Routes
    Route::get('/admin/client-uploads', [AdminClientUploadController::class, 'getUploads'])->name('api.admin.uploads');
    Route::get('/admin/processing-requests', [AdminClientUploadController::class, 'getProcessingRequests'])->name('api.admin.processing_requests');
    Route::get('/admin/client-uploads/path-config', [AdminClientUploadController::class, 'getPathConfig'])->name('api.admin.path_config');
    Route::post('/admin/client-uploads/{id}/decision', [AdminClientUploadController::class, 'submitDecision'])->name('api.admin.decision');
    Route::delete('/admin/client-uploads/{id}', [AdminClientUploadController::class, 'deleteUpload'])->name('api.admin.delete_upload');
    Route::post('/admin/client-uploads/delete-multiple', [AdminClientUploadController::class, 'deleteMultipleUploads'])->name('api.admin.delete_multiple_uploads');
    Route::post('/admin/processing-requests/{id}/delivery', [AdminClientUploadController::class, 'markDelivered'])->name('api.admin.delivery');
    Route::post('/admin/processing-requests/{id}/update-notes', [AdminClientUploadController::class, 'updateDeliveryNotes'])->name('api.admin.update_notes');
    Route::post('/admin/client-uploads/{id}/ensure-delivery-folder', [AdminClientUploadController::class, 'ensureDeliveryFolder'])->name('api.admin.ensure_folder');

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
