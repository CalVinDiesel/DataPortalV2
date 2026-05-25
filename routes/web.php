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

Route::post('/contact', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:5000',
    ]);

    try {
        $supportEmail = config('support.email');
        Mail::to($supportEmail)->send(new ContactInquiry(
            $request->name,
            $request->email,
            $request->message
        ));
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
    return view('portal.request-access');
})->name('request_access');

Route::post('/request-access', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'company_name' => 'nullable|string|max:255',
        'reason_for_access' => 'nullable|string|max:1000',
    ]);

    // Check if user already exists in main portal DB
    if (\App\Models\User::where('email', $request->email)->exists()) {
        return back()->withErrors(['email' => 'An account with this email already exists. Please log in.'])->withInput();
    }

    // Check if they already have any request in the access_requests table
    $existingRequest = \App\Models\AccessRequest::where('email', $request->email)->first();
    if ($existingRequest) {
        if ($existingRequest->status === 'pending') {
            return back()->withErrors(['email' => 'You already have a pending access request. Please wait for approval.'])->withInput();
        } elseif ($existingRequest->status === 'approved') {
            return back()->withErrors(['email' => 'Your request has already been approved. Please check your email for the setup link or log in.'])->withInput();
        } else {
            return back()->withErrors(['email' => 'A request for this email has already been processed. Please contact support if you need further assistance.'])->withInput();
        }
    }

    \App\Models\AccessRequest::create([
        'name' => $request->name,
        'email' => $request->email,
        'company_name' => $request->company_name,
        'reason_for_access' => $request->reason_for_access,
        'status' => 'pending',
    ]);

    // Send confirmation to User
    try {
        Mail::to($request->email)->send(new RequestReceived($request->name));
        
        // Send alert to Admin
        $adminEmail = env('SUPER_ADMIN_EMAIL', 'mosestiquan23@gmail.com');
        Mail::to($adminEmail)->send(new NewRequestAlert(
            $request->name, 
            $request->email, 
            $request->company_name, 
            $request->reason_for_access
        ));
    } catch (\Exception $e) {
        \Log::error('Mail sending failed on Request Access', ['error' => $e->getMessage()]);
        // We continue anyway as the DB record was saved
    }

    return back()->with('success', 'Your request has been received. Our team will review it shortly, and you will receive an email if your access is approved.');
});

use App\Http\Controllers\SetupController;
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup', [SetupController::class, 'process'])->name('setup.process');

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
        return view('portal.create-project');
    })->name('create_project');

    Route::get('/upload-data', function () {
        return view('portal.upload-data');
    })->name('upload_data');

    Route::get('/upload-sftp', function (Request $request) {
        $role = $request->user()->role;
        if (!in_array($role, ['trusted', 'admin'])) {
            return redirect()->route('create_project')->with('error', 'SFTP upload is only available for trusted users.');
        }
        return view('portal.upload-sftp');
    })->name('upload_sftp');

    // 🚀 CLOUD HUB (v265): Unified redirect for GDrive and OneDrive
    Route::get('/upload-cloud', function () {
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

    // 🚀 SESSION-SYNC (v271): Moved from api.php to ensure stable session access for AJAX
    Route::get('/api/user/my-uploads', [ProjectController::class, 'index']);
    Route::post('/api/user/my-uploads/{id}/confirm-received', [ProjectController::class, 'confirmReceived']);
    Route::post('/api/user/my-uploads/{id}/sync-metadata', [ProjectController::class, 'syncSftpMetadata']);
    Route::post('/api/user/my-uploads/{id}/sync-gdrive', [ProjectController::class, 'syncGoogleDriveMetadata']);
    Route::post('/api/user/my-uploads/{id}/sync-onedrive', [ProjectController::class, 'syncOneDriveMetadata']);
    Route::get('/api/user/my-uploads/{id}/download-delivered', [ProjectController::class, 'downloadDelivered']);
    Route::post('/api/user/my-uploads/{id}/accept-disclaimer', [ProjectController::class, 'acceptDisclaimer']);
    Route::delete('/api/user/my-uploads/{id}', [ProjectController::class, 'destroy']);
    Route::patch('/api/user/my-uploads/{id}', [ProjectController::class, 'update']);

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

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

    Route::get('/admin/client-uploads', function () {
        return view('admin.client-uploads');
    })->name('admin.client_uploads');

    Route::get('/admin/manage-users', function () {
        return view('admin.manage-users');
    })->name('admin.manage_users');

    Route::post('/admin/client-uploads/check-sftp-status', [UploadController::class, 'checkSftpStatus'])
        ->name('admin.client_uploads.check_sftp_status');

    Route::post('/admin/client-uploads/retry-sftp', [UploadController::class, 'retrySftpHandover'])
        ->name('admin.client_uploads.retry_sftp');
});

Route::post('/upload/pin-image', [UploadController::class, 'uploadPinImage'])

    ->name('upload.pin-image');

require __DIR__.'/auth.php';
