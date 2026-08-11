<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    // Tambahkan method index() ini
    public function index()
    {
        try {
            $transaksis = Transaksi::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil mengambil data transaksi',
                'data' => $transaksis
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payment(Request $request)
    {
        $request->validate([
            'kode_tiket' => 'required',
            'uang_bayar' => 'required|numeric|min:0',
        ]);

        $tarif = 5000;
        $uangBayar = $request->uang_bayar;

        if ($uangBayar < $tarif) {
            return response()->json([
                'status' => false,
                'message' => 'Uang bayar kurang!'
            ], 400);
        }

        $kembalian = $uangBayar - $tarif;

        $transaksi = Transaksi::create([
            'kode_tiket' => $request->kode_tiket,
            'kategori' => $request->kategori ?? 'motor',
            'no_plat' => $request->no_plat ?? '-',
            'total_bayar' => $tarif,
            'uang_bayar' => $uangBayar,
            'kembalian' => $kembalian,
            'status' => 'lunas',
            'tanggal_bayar' => Carbon::now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pembayaran berhasil',
            'data' => $transaksi
        ], 200);
    }
}