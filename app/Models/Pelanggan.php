<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $primaryKey = 'id';

    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'jenis_kelamin',
        'alamat',
        'no_hp',
        'email',
        'password',
        'tanggal_lahir',
        'pekerjaan',
        'tanggal_daftar',
        'status'
    ];

    protected $hidden = [
        'password', // <--- agar password aman, tidak ditampilkan saat response JSON
    ];

    protected $casts = [
        'password' => 'hashed', // Lumen akan otomatis menghash password saat disimpan
    ];

    public $timestamps = true;

    // Relasi One to Many
    public function pesananLaundry()
    {
        return $this->hasMany(
            PesananLaundry::class,
            'pelanggan_id',
            'id'
        );
    }
}
