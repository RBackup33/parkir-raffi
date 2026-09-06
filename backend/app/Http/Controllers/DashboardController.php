<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\TiketParkir;
use App\Models\Member;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try { // 🔥 Pastikan try ada di sini
            $memberAktif = Member::where('status', 'lunas')->count();
            $kendaraanHariIni = TiketParkir::whereDate('created_at', Carbon::today())->count();
            $pendapatanHariIni = Transaksi::whereDate('created_at', Carbon::today())->sum('total_bayar');
            
            // Menghitung jumlah tiket yang belum keluar (aktif parkir)
            $sedangParkir = TiketParkir::where('status', '!=', 'keluar')->count();

            $transaksiTerbaru = Transaksi::orderBy('created_at', 'desc')->take(5)->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'member_aktif' => $memberAktif,
                    'kendaraan_hari_ini' => $kendaraanHariIni,
                    'pendapatan_hari_ini' => $pendapatanHariIni,
                    'sedang_parkir' => $sedangParkir,
                    'transaksi_terbaru' => $transaksiTerbaru
                ]
            ], 200);

        } catch (\Exception $e) { // 🔥 Pasangan try di atas
            return response()->json([
                'status' => false,
                'message' => 'Error Dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}