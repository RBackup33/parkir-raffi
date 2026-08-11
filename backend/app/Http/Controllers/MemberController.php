<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MemberController extends Controller
{
    // ===================================
    // TAMPIL SEMUA MEMBER
    // ===================================
    public function index()
    {
        $member = Member::latest()->get();

        return response()->json([
            "status" => true,
            "data" => $member
        ]);
    }

    // ===================================
    // TAMBAH MEMBER
    // ===================================
    public function store(Request $request)
    {
        $request->validate([
            'nama_member' => 'required',
            'nama_perusahaan' => 'required',
            'uang_bayar' => 'required|numeric|min:0'
        ]);

        $harga_member = 150000;
        $uang_bayar = $request->uang_bayar;

        // hitung kembalian
        $kembalian = $uang_bayar - $harga_member;

        if ($kembalian < 0) {
            $kembalian = 0;
        }

        // status otomatis
        $status = $uang_bayar >= $harga_member 
            ? "lunas" 
            : "belum lunas";

        $member = Member::create([
            'kode_member' => $this->generateKodeMember(),
            'token' => Str::uuid(),
            'nama_member' => $request->nama_member,
            'nama_perusahaan' => $request->nama_perusahaan,
            'total_harga' => $harga_member,
            'kembalian' => $kembalian,
            'status' => $status,
            'tanggal_mulai' => Carbon::now(),
            'tanggal_expired' => Carbon::now()->endOfMonth()
        ]);

        return response()->json([
            "status" => true,
            "message" => "Member berhasil dibuat",
            "data" => $member
        ], 201);
    }

    // ===================================
    // GENERATE KODE MEMBER
    // ===================================
    private function generateKodeMember()
    {
        do {
            $kode = 'MBR-' . strtoupper(Str::random(6));
        } while (
            Member::where('kode_member', $kode)->exists()
        );

        return $kode;
    }

    // ===================================
    // DETAIL MEMBER + QR
    // ===================================
    public function show($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                "status" => false,
                "message" => "Member tidak ditemukan"
            ], 404);
        }

        // Generate QR dari token
        $qr = base64_encode(
            QrCode::format('png')
                ->size(300)
                ->generate(
                    $member->token
                )
        );

        return response()->json([
            "status" => true,
            "data" => [
                "id" => $member->id,
                "kode_member" => $member->kode_member,
                "token" => $member->token,
                "nama_member" => $member->nama_member,
                "nama_perusahaan" => $member->nama_perusahaan,
                "total_harga" => $member->total_harga,
                "kembalian" => $member->kembalian,
                "status" => $member->status,
                "tanggal_mulai" => $member->tanggal_mulai,
                "tanggal_expired" => $member->tanggal_expired,
                "qr" => "data:image/png;base64," . $qr
            ]
        ]);
    }

    // ===================================
    // UPDATE MEMBER
    // ===================================
    public function update(Request $request, $id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                "status" => false,
                "message" => "Member tidak ditemukan"
            ], 404);
        }

        $request->validate([
            'nama_member' => 'required',
            'nama_perusahaan' => 'required'
        ]);

        $member->update([
            'nama_member' => $request->nama_member,
            'nama_perusahaan' => $request->nama_perusahaan
        ]);

        return response()->json([
            "status" => true,
            "message" => "Data member berhasil diperbarui"
        ]);
    }

    // ===================================
    // HAPUS MEMBER
    // ===================================
    public function destroy($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json([
                "status" => false,
                "message" => "Member tidak ditemukan"
            ], 404);
        }

        $member->delete();

        return response()->json([
            "status" => true,
            "message" => "Member berhasil dihapus"
        ]);
    }

    // ===================================
    // SCAN QR MEMBER
    // ===================================
    public function check(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $member = Member::where('token', $request->token)->first();

        if (!$member) {
            return response()->json([
                "status" => false,
                "message" => "QR Member tidak ditemukan"
            ]);
        }

        // cek expired
        if (Carbon::now()->greaterThan(Carbon::parse($member->tanggal_expired))) {
            return response()->json([
                "status" => false,
                "message" => "Member sudah expired"
            ]);
        }

        // cek pembayaran
        if ($member->status != "lunas") {
            return response()->json([
                "status" => false,
                "message" => "Member belum melakukan pembayaran"
            ]);
        }

        return response()->json([
            "status" => true,
            "message" => "Member valid",
            "data" => $member
        ]);
    }

    // ===================================
    // RESET BULANAN
    // ===================================
    public function resetBulanan()
    {
        if (Carbon::now()->day == 1) {
            Member::query()->update([
                'status' => 'belum lunas'
            ]);
        }

        return response()->json([
            "status" => true,
            "message" => "Status member diperbarui"
        ]);
    }
}