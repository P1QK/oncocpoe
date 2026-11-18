<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedcytoController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\RegimenController;
use App\Models\Medcyto;
use App\Models\OncDayRecord;
use App\Models\OrderHeader;
use App\Models\RegimenHeader;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to home or login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : redirect()->route('login');
});

// Guest / Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Central Hub / Home (shows only sections permitted for the current user's role)
    Route::get('/home', function () {
        $stats = [
            'orders_total'     => OrderHeader::count(),
            'orders_pending'   => OrderHeader::where('or_confirm', 0)->count(),
            'orders_confirmed' => OrderHeader::where('or_confirm', 1)->count(),
            'regimens_count'   => RegimenHeader::where('rh_used', 1)->count(),
            'meds_count'       => Medcyto::count(),
            'today_doses'      => OncDayRecord::whereDate('or_daterec', today())->count(),
            'users_count'      => User::count(),
        ];
        return view('home', compact('stats'));
    })->name('home');

    // 1. SUPERADMIN ONLY: User Management (onc_users)
    Route::middleware('role:superadmin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [AuthController::class, 'usersIndex'])->name('index');
        Route::post('/', [AuthController::class, 'storeUser'])->name('store');
        Route::delete('/{id}', [AuthController::class, 'destroyUser'])->name('destroy');
    });

    // 2. DOCTOR SYSTEM (Accessible by superadmin, admin, doctor)
    Route::middleware('role:superadmin,admin,doctor')->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/create', [DoctorController::class, 'create'])->name('create');
        Route::post('/store', [DoctorController::class, 'store'])->name('store');
        Route::get('/order/{orId}', [DoctorController::class, 'show'])->name('show');
    });

    // 3. PHARMACIST SYSTEM (Accessible by superadmin, admin, pharmacist)
    Route::middleware('role:superadmin,admin,pharmacist')->prefix('pharmacist')->name('pharmacist.')->group(function () {
        Route::get('/', [PharmacistController::class, 'index'])->name('index');
        Route::get('/poll', [PharmacistController::class, 'poll'])->name('poll');
        Route::get('/stickers', [PharmacistController::class, 'dailyStickers'])->name('stickers');
        Route::get('/order/{orId}', [PharmacistController::class, 'show'])->name('show');
        Route::post('/order/{orId}/sequence', [PharmacistController::class, 'updateSequence'])->name('sequence');
        Route::post('/order/{orId}/confirm', [PharmacistController::class, 'confirm'])->name('confirm');
        Route::post('/order/{orId}/day-status', [PharmacistController::class, 'updateDayStatus'])->name('day-status');
        Route::get('/order/{orId}/print/working-formula', [PharmacistController::class, 'workingFormula'])->name('print.working-formula');
        Route::get('/order/{orId}/print/labels', [PharmacistController::class, 'medicationLabels'])->name('print.labels');
    });

    // 4. NURSE SYSTEM (Accessible by superadmin, admin, nurse)
    Route::middleware('role:superadmin,admin,nurse')->prefix('nurse')->name('nurse.')->group(function () {
        Route::get('/', [NurseController::class, 'index'])->name('index');
        Route::post('/acknowledge', [NurseController::class, 'acknowledge'])->name('acknowledge');
    });

    // 5. REGIMEN MASTER DATA (Accessible by superadmin and admin ONLY)
    Route::middleware('role:superadmin,admin')->prefix('regimens')->name('regimens.')->group(function () {
        Route::get('/', [RegimenController::class, 'index'])->name('index');
        Route::post('/', [RegimenController::class, 'store'])->name('store');
        Route::post('/{rhId}/sequence', [RegimenController::class, 'updateSequence'])->name('sequence');
        Route::delete('/{rhId}', [RegimenController::class, 'destroy'])->name('destroy');
    });

    // 6. MEDCYTO MASTER DATA (Accessible by superadmin and admin ONLY)
    Route::middleware('role:superadmin,admin')->prefix('medcyto')->name('medcyto.')->group(function () {
        Route::get('/', [MedcytoController::class, 'index'])->name('index');
        Route::post('/', [MedcytoController::class, 'store'])->name('store');
        Route::put('/{dgCode}', [MedcytoController::class, 'update'])->name('update');
        Route::delete('/{dgCode}', [MedcytoController::class, 'destroy'])->name('destroy');
    });
});
