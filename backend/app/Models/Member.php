<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{

protected $table="members";


protected $fillable = [
    'kode_member',
    'token',
    'nama_member',
    'nama_perusahaan',
    'total_harga',
    'kembalian',
    'status',
    'tanggal_mulai',
    'tanggal_expired'
];
}
