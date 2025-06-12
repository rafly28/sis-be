<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Murid extends Model
{
    use HasFactory;

        protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'jenis_kelamin',
        'agama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'tahun_masuk',
        'status',
        'orang_tua_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'orang_tua_id');
    }
}
