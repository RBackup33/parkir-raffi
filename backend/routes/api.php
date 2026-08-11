<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TiketController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransaksiController;

// tiket
Route::post('/tiket', [TiketController::class, 'create']);

// qrcode (Tambahkan baris ini)
Route::get('/qrcode/{kode}', [TiketController::class, 'qrcode']);

// transaksi
Route::post('/scan', [ScanController::class, 'scan']);
Route::post('/payment', [PaymentController::class, 'bayar']);

// member
Route::post('/member/check', [MemberController::class, 'check']);
Route::apiResource('/member', MemberController::class);

// Route Transaksi Parkir
Route::prefix('transaksi')->group(function () {
    Route::get('/', [TransaksiController::class, 'index']);
    Route::post('/payment', [TransaksiController::class, 'payment']);
});