<?php

namespace App\Http\Controllers;

use App\Models\TiketParkir;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TiketController extends Controller
{
    /**
     * Generate gambar SVG QR Code secara dynamic berdasarkan kode tiket
     */
    public function qrcode($kode)
    {
        return response(QrCode::format('svg')->size(150)->generate($kode))
            ->header('Content-Type', 'image/svg+xml');
    }

    public function create()
    {
        $randomNum = mt_rand(100, 999);
        $kode = "A" . $randomNum;

        while (TiketParkir::where('kode_tiket', $kode)->exists()) {
            $randomNum = mt_rand(100, 999);
            $kode = "A" . $randomNum;
        }

        // Generate QR Code dari $kode dalam bentuk SVG Data URI (Base64)
        $qrSvg = QrCode::format('svg')->size(200)->generate($kode);
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        $tiket = TiketParkir::create([
            'kode_tiket'   => $kode,
            'qr_code'      => $qrCodeBase64, // Menyimpan string base64 QR Code
            'status'       => 'masuk',
            'waktu_masuk'  => now(),
            'waktu_keluar' => null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $tiket
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
            'success' => true,
            'data' => $tiket
        ]);
    }

    public function index()
    {
        return response()->json(
            TiketParkir::latest()->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            TiketParkir::findOrFail($id)
        );
    }

    public function destroy($id)
    {
        $tiket = TiketParkir::findOrFail($id);

        $tiket->delete();

        return response()->json([
            'success' => true
        ]);
    }
}