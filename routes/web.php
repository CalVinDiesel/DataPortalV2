<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('portal.landing-page');
})->name('landing');

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

    Route::get('/upload-sftp', function () {
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
        return view('admin.dashboard');
    })->name('admin_dashboard');
});

require __DIR__.'/auth.php';
