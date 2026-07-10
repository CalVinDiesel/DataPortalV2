<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

use Illuminate\Support\Facades\Mail;
use App\Mail\RequestReceived;
use App\Mail\NewRequestAlert;
use App\Mail\ContactInquiry;
use App\Http\Controllers\ProjectController;

Route::get('/health', function () {
    return response('OK', 200);
});

Route::post('/contact', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:5000',
    ]);

    try {
        $adminEmails = \App\Models\User::getAdminEmails();
        $supportEmail = config('support.email');
        if ($supportEmail) {
            $adminEmails[] = $supportEmail;
        }
        $adminEmails = array_values(array_unique(array_filter($adminEmails)));

        foreach ($adminEmails as $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new ContactInquiry(
                    $request->name,
                    $request->email,
                    $request->message
                ));
            } catch (\Exception $e) {
                \Log::error('Contact form mail failed for ' . $adminEmail, ['error' => $e->getMessage()]);
            }
        }
        return back()->with('success', 'Your message has been sent successfully! We will get back to you shortly.');
    } catch (\Exception $e) {
        \Log::error('Contact form submission failed', ['error' => $e->getMessage()]);
        return back()->with('error', 'Sorry, there was an error sending your message. Please try again later.');
    }
})->name('contact.submit');


Route::get('/', function () {
    return view('portal.landing-page');
})->name('landing');

Route::get('/request-access', function () {
    return redirect()->route('landing');
})->name('request_access');

Route::post('/request-access', function () {
    return redirect()->route('landing');
});

use App\Http\Controllers\SetupController;
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup', [SetupController::class, 'process'])->name('setup.process');
Route::get('/activate', [SetupController::class, 'index'])->name('activate.index');
Route::post('/activate', [SetupController::class, 'process'])->name('activate.process');

use App\Http\Controllers\ProxyController;

Route::get('/proxy', [ProxyController::class, 'proxy'])->name('proxy');

Route::get('/viewer/{id}', function ($id) {
    return view('portal.3D-viewer', ['id' => $id]);
})->name('viewer');

Route::get('/loading-3d', function (Request $request) {
    return view('portal.loading-3d', ['id' => $request->query('id')]);
})->name('loading_3d');

// Fallback for legacy underscored URL format used in some JS components
Route::get('/loading_3d/{id}', function ($id) {
    return view('portal.loading-3d', ['id' => $id]);
});

Route::get('/pricing', function () {
    return view('portal.pricing-page');
})->name('pricing');

Route::get('/payment', function () {
    return view('portal.payment-page');
})->name('payment');

Route::middleware('auth')->group(function () {

    Route::get('/create-project', function () {
        if (\App\Models\ClientUpload::hasExceededStorageLimit(auth()->user()->email)) {
            return redirect()->route('my_uploads')->with('error', 'Storage Quota Exceeded. You cannot create a new project because your storage is full. Please delete past projects to free up space.');
        }
        return view('portal.create-project');
    })->name('create_project');

    Route::get('/upload-data', function () {
        if (\App\Models\ClientUpload::hasExceededStorageLimit(auth()->user()->email)) {
            return redirect()->route('my_uploads')->with('error', 'Storage Quota Exceeded. You cannot upload files because your storage is full. Please delete past projects to free up space.');
        }
        return view('portal.upload-data');
    })->name('upload_data');

    Route::get('/upload-sftp', function (Request $request) {
        if (\App\Models\ClientUpload::hasExceededStorageLimit($request->user()->email)) {
            return redirect()->route('my_uploads')->with('error', 'Storage Quota Exceeded. You cannot use SFTP upload because your storage is full. Please delete past projects to free up space.');
        }
        $role = $request->user()->role;
        if (!in_array($role, ['trusted', 'admin'])) {
            return redirect()->route('create_project')->with('error', 'SFTP upload is only available for trusted users.');
        }
        return view('portal.upload-sftp');
    })->name('upload_sftp');

    // 🚀 CLOUD HUB (v265): Unified redirect for GDrive and OneDrive
    Route::get('/upload-cloud', function () {
        if (\App\Models\ClientUpload::hasExceededStorageLimit(auth()->user()->email)) {
            return redirect()->route('my_uploads')->with('error', 'Storage Quota Exceeded. You cannot use Cloud upload because your storage is full. Please delete past projects to free up space.');
        }
        return view('portal.upload-cloud');
    })->name('upload_cloud');

    Route::get('/upload-gdrive', function () {
        return redirect()->route('upload_cloud');
    })->name('upload_gdrive');

    Route::get('/my-uploads', function () {
        return view('portal.my-uploads');
    })->name('my_uploads');

    Route::get('/profile', function () {
        return view('portal.user-profile');
    })->name('profile');

    // Inquiry Routes
    Route::get('/inquiry/new', [\App\Http\Controllers\InquiryController::class, 'create'])->name('inquiry.new');
    Route::post('/inquiry/store', [\App\Http\Controllers\InquiryController::class, 'store'])->name('inquiry.store');
    Route::get('/inquiry/my', [\App\Http\Controllers\InquiryController::class, 'my'])->name('inquiry.my');
    Route::get('/inquiry/{id}/edit', [\App\Http\Controllers\InquiryController::class, 'edit'])->name('inquiry.edit');
    Route::post('/inquiry/{id}/update', [\App\Http\Controllers\InquiryController::class, 'update'])->name('inquiry.update');
    Route::delete('/inquiry/{id}/delete', [\App\Http\Controllers\InquiryController::class, 'destroy'])->name('inquiry.destroy');
    Route::get('/api/inquiry/{id}/download', [\App\Http\Controllers\InquiryController::class, 'clientDownload'])->name('inquiry.download');
    Route::get('/api/inquiry/{id}/quotation-pdf', [\App\Http\Controllers\InquiryController::class, 'clientDownloadQuotationPdf'])->name('inquiry.pdf');
    Route::post('/api/inquiry/{id}/payment-receipt', [\App\Http\Controllers\InquiryController::class, 'clientUploadReceipt'])->name('inquiry.upload_receipt');
    Route::get('/api/inquiry/{id}/payment-receipt', [\App\Http\Controllers\InquiryController::class, 'clientDownloadPaymentReceipt'])->name('inquiry.receipt');
    Route::get('/api/inquiry/{id}/status', [\App\Http\Controllers\InquiryController::class, 'clientCheckStatus'])->name('inquiry.status');
    Route::post('/api/inquiry/{id}/accept-disclaimer', [\App\Http\Controllers\InquiryController::class, 'acceptDisclaimer'])->name('inquiry.accept_disclaimer');
    Route::get('/api/inquiry/{id}/preview-tileset', [\App\Http\Controllers\InquiryController::class, 'getInquiryPreviewTilesetConfig'])->name('inquiry.preview_tileset');

    // 🚀 SESSION-SYNC (v271): Moved from api.php to ensure stable session access for AJAX
    Route::get('/api/user/my-uploads', [ProjectController::class, 'index']);
    Route::get('/api/user/storage-quota', [ProjectController::class, 'getStorageQuota']);
    Route::post('/api/user/my-uploads/{id}/confirm-received', [ProjectController::class, 'confirmReceived']);
    Route::post('/api/user/my-uploads/{id}/sync-metadata', [ProjectController::class, 'syncSftpMetadata']);
    Route::post('/api/user/my-uploads/{id}/sync-gdrive', [ProjectController::class, 'syncGoogleDriveMetadata']);
    Route::post('/api/user/my-uploads/{id}/sync-onedrive', [ProjectController::class, 'syncOneDriveMetadata']);
    Route::get('/api/user/my-uploads/{id}/download-delivered', [ProjectController::class, 'downloadDelivered']);
    Route::post('/api/user/my-uploads/{id}/accept-disclaimer', [ProjectController::class, 'acceptDisclaimer']);
    Route::delete('/api/user/my-uploads/{id}', [ProjectController::class, 'destroy']);
    Route::patch('/api/user/my-uploads/{id}', [ProjectController::class, 'update']);
    
    Route::get('/api/user/my-uploads/{id}/preview-tileset', [ProjectController::class, 'getPreviewTilesetConfig']);

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/viewer-assets/{path}', [ProjectController::class, 'streamViewerAsset'])->where('path', '.*')->name('viewer_assets');

Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.index');
    })->name('admin_dashboard');

    Route::get('/admin/add-3d-model', function () {
        return view('admin.add-3d-model');
    })->name('admin.add_3d_model');

    Route::get('/admin/manage-map-pins', function () {
        return view('admin.manage-map-pins');
    })->name('admin.manage_map_pins');

    Route::get('/admin/manage-showcases', function () {
        return view('admin.manage-showcases');
    })->name('admin.manage_showcases');

    // RESTORED: Client Uploads management route
    Route::get('/admin/client-uploads', function () {
        return view('admin.client-uploads');
    })->name('admin.client_uploads');

    Route::get('/admin/manage-users', function () {
        return view('admin.manage-users');
    })->name('admin.manage_users');

    // Inquiry Admin Routes
    Route::get('/admin/inquiries', [\App\Http\Controllers\InquiryController::class, 'adminIndex'])->name('admin.inquiries');
    Route::get('/api/admin/inquiries', [\App\Http\Controllers\InquiryController::class, 'adminList']);
    Route::get('/api/admin/inquiries/{id}', [\App\Http\Controllers\InquiryController::class, 'adminShow']);
    Route::delete('/api/admin/inquiries/{id}', [\App\Http\Controllers\InquiryController::class, 'adminDestroy']);
    Route::patch('/api/admin/inquiries/{id}/status', [\App\Http\Controllers\InquiryController::class, 'adminUpdateStatus']);
    Route::post('/api/admin/inquiries/{id}/status', [\App\Http\Controllers\InquiryController::class, 'adminUpdateStatus']);
    Route::patch('/api/admin/inquiries/{id}/delivery', [\App\Http\Controllers\InquiryController::class, 'adminToggleDelivery']);
    Route::get('/api/admin/inquiries/{id}/check-delivery', [\App\Http\Controllers\InquiryController::class, 'adminCheckDelivery']);
    Route::get('/api/admin/inquiries/{id}/quotation-pdf', [\App\Http\Controllers\InquiryController::class, 'adminStreamQuotationPdf']);
    Route::get('/api/admin/inquiries/{id}/payment-receipt', [\App\Http\Controllers\InquiryController::class, 'adminStreamPaymentReceipt']);
    Route::post('/admin/client-uploads/check-sftp-status', [UploadController::class, 'checkSftpStatus'])
        ->name('admin.client_uploads.check_sftp_status');

    Route::post('/admin/client-uploads/retry-sftp', [UploadController::class, 'retrySftpHandover'])
        ->name('admin.client_uploads.retry_sftp');

    Route::get('/api/admin/inquiries/{id}/download-kml', [\App\Http\Controllers\InquiryController::class, 'adminDownloadKml']);
});
Route::post('/upload/pin-image', [UploadController::class, 'uploadPinImage'])

    ->name('upload.pin-image');

require __DIR__.'/auth.php';
