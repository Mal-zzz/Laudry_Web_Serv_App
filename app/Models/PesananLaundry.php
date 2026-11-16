<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananLaundry extends Model
{
    protected $table = 'pesanan_laundry';

    protected $primaryKey = 'id';

    protected $fillable = [
        'pelanggan_id',
        'jenis_layanan',
        'berat_kg',
        'total_harga',
        'status_pesanan',
        'tanggal_masuk',
        'tanggal_selesai'
    ];

    // Relasi Many to One
    public function pelanggan()
    {
        return $this->belongsTo(
            Pelanggan::class,
            'pelanggan_id',
            'id'
        );
    }
}
