<?php

namespace App\Http\Controllers;

use App\Models\TiketParkir;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class TiketController extends Controller
{
   public function qrcode($kode)
    {
        if (ob_get_length()) {
            ob_clean();
        }

        $qrSvg = trim(QrCode::format('svg')->size(150)->generate($kode));

        return response($qrSvg, 200)
            ->header('Content-Type', 'image/svg+xml; charset=utf-8');
    }

    /**
     * Buat Tiket Masuk Baru (Pintu Masuk User)
     */
    public function create()
    {
        try {
            $randomNum = mt_rand(100, 999);
            $kode = "A" . $randomNum;

            while (TiketParkir::where('kode_tiket', $kode)->exists()) {
                $randomNum = mt_rand(100, 999);
                $kode = "A" . $randomNum;
            }

   $qrSvg = QrCode::format('svg')->size(200)->generate($kode);
            $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

            $tiket = TiketParkir::create([
                'kode_tiket'   => $kode,
                'qr_code'      => $qrCodeBase64,
                'status'       => 'masuk',
                'waktu_masuk'  => now(),
                'waktu_keluar' => null,
            ]);
            
            return response()->json([
                'status'  => true,
                'success' => true,
                'message' => 'Tiket berhasil dibuat',
                'data'    => $tiket
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'success' => false,
                'message' => 'Gagal membuat tiket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showByKode($kode)
    {
        $tiket = TiketParkir::where('kode_tiket', $kode)->first();

        if (!$tiket) {
            return response()->json([
                'status'  => false,
                'message' => 'Kode tiket tidak ditemukan!'
            ], 404);
        }

        $waktuMasuk = Carbon::parse($tiket->waktu_masuk);
        $waktuSekarang = Carbon::now();
        $durasiJam = ceil($waktuMasuk->diffInMinutes($waktuSekarang) / 60);
        if ($durasiJam < 1) $durasiJam = 1;

        return response()->json([
            'status' => true,
            'data'   => [
                'id'          => $tiket->id,
                'kode_tiket'  => $tiket->kode_tiket,
                'waktu_masuk' => $waktuMasuk->format('H:i:s d-m-Y'),
                'durasi_jam'  => $durasiJam,
                'status'      => $tiket->status
            ]
        ]);
    }

    public function keluar($id)
    {
        $tiket = TiketParkir::findOrFail($id);

        $tiket->update([
            'status' => 'keluar',
            'waktu_keluar' => now(),
        ]);

        return response()->json([
            'status' => true,
            'success' => true,
            'data' => $tiket
        ]);
    }

    public function index()
    {
        return response()->json([
            'status' => true,
            'data'   => TiketParkir::latest()->get()
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'status' => true,
            'data'   => TiketParkir::findOrFail($id)
        ]);
    }

   // Di TiketController.php atau TransaksiController.php
public function kendaraanAktif()
{
    // Mengambil tiket yang statusnya BELUM 'keluar'
    $data = TiketParkir::where('status', '!=', 'keluar')
                ->orderBy('created_at', 'desc')
                ->get();

    return response()->json([
        'status' => true,
        'message' => 'Daftar kendaraan yang masih parkir',
        'data' => $data
    ], 200);
}

    public function destroy($id)
    {
        $tiket = TiketParkir::findOrFail($id);
        $tiket->delete();

        return response()->json([
            'status'  => true,
            'success' => true
        ]);
    }
}