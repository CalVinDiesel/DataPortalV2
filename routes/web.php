<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

use Illuminate\Support\Facades\Mail;
use App\Mail\RequestReceived;
use App\Mail\NewRequestAlert;

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

    // Check if they already have any request in the AccessRequests table
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

    Route::get('/upload-gdrive', function () {
        return view('portal.upload-gdrive');
    })->name('upload_gdrive');

    Route::get('/my-uploads', function () {
        return view('portal.my-uploads');
    })->name('my_uploads');

    Route::get('/profile', function () {
        return view('portal.user-profile');
    })->name('profile');

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

    Route::get('/admin/manage-showcase', function () {
        return view('admin.manage-showcase');
    })->name('admin.manage_showcase');

    Route::get('/admin/client-uploads', function () {
        return view('admin.client-uploads');
    })->name('admin.client_uploads');

    Route::get('/admin/manage-users', function () {
        return view('admin.manage-users');
    })->name('admin.manage_users');
});

Route::post('/upload/pin-image', [UploadController::class, 'uploadPinImage'])
    ->name('upload.pin-image');

require __DIR__.'/auth.php';
