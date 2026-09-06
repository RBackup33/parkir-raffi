<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\TiketParkir;
use Carbon\Carbon;

class MemberGateController extends Controller
{
    public function scanGate(Request $request)
    {
        $request->validate([
            'kode' => 'required',
        ]);

        $kode = trim($request->kode);

        // 1. Cek apakah member
        $member = Member::where('kode_member', $kode)->first();
        if ($member) {
            $isExpired = $member->tanggal_expired && Carbon::parse($member->tanggal_expired)->isPast();

            if ($member->status !== 'lunas' || $isExpired) {
                return response()->json(['message' => 'Member sudah tidak aktif, expired, atau belum lunas!'], 400);
            }

            return response()->json([
                'type' => 'member',
                'message' => 'Akses Gate Dibuka (Member)',
                'data' => $member
            ], 200);
        }

        // 2. Cek apakah tiket parkir umum
        $tiket = TiketParkir::where('kode_tiket', $kode)->first();
        if ($tiket) {
            if ($tiket->status === 'keluar') {
                return response()->json(['message' => 'Tiket sudah digunakan keluar!'], 400);
            }

            $waktuMasuk = Carbon::parse($tiket->created_at);
            $waktuKeluar = Carbon::now();
            
            $selisihMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
            $durasiJam = max(1, (int) ceil($selisihMenit / 60));

            return response()->json([
                'type' => 'tiket',
                'message' => 'Tiket Valid, Silakan Lanjut Pembayaran',
                'data' => [
                    'id' => $tiket->id,
                    'kode_tiket' => $tiket->kode_tiket,
                    'waktu_masuk' => $tiket->created_at,
                    'durasi_jam' => $durasiJam // Durasi yang sudah dihitung akurat dikirim ke frontend
                ]
            ], 200);
        }

        return response()->json(['message' => 'Kode Member / Tiket tidak valid!'], 404);
    }
}