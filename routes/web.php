<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PengajuanController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Staff
    Route::middleware('role:staff')->group(function () {
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/create', [PengajuanController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanController::class, 'store'])->name('pengajuan.store');
        Route::get('/pengajuan/{pengajuan}/edit', [PengajuanController::class, 'edit'])->name('pengajuan.edit');
        Route::put('/pengajuan/{pengajuan}', [PengajuanController::class, 'update'])->name('pengajuan.update');
        Route::post('/pengajuan/{pengajuan}/submit', [PengajuanController::class, 'submit'])->name('pengajuan.submit');
    });

    // Detail pengajuan bisa dilihat staff (pemilik) & seluruh approver terkait
    Route::get('/pengajuan/{pengajuan}', [PengajuanController::class, 'show'])->name('pengajuan.show');

    // SPV, Manager, Direktur approval queue
    Route::middleware('role:spv,manager,direktur')->prefix('approval')->group(function () {
        Route::get('/{role}', [ApprovalController::class, 'index'])->name('approval.index');
        Route::get('/{role}/{pengajuan}', [ApprovalController::class, 'show'])->name('approval.show');
        Route::post('/{role}/{pengajuan}/decide', [ApprovalController::class, 'decide'])->name('approval.decide');
    });

    // Finance
    Route::middleware('role:finance')->prefix('finance')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/{pengajuan}', [FinanceController::class, 'show'])->name('finance.show');
        Route::post('/{pengajuan}/proses', [FinanceController::class, 'proses'])->name('finance.proses');
    });
});
