<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PesananLaundry;

class PesananLaundrySeeder extends Seeder
{
    public function run()
    {
        PesananLaundry::create([
            'pelanggan_id' => 1,
            'jenis_layanan' => 'Cuci Kering',
            'berat_kg' => 3.00,
            'total_harga' => 15000,
            'status_pesanan' => 'selesai',
            'tanggal_masuk' => '2026-11-10 08:00:00',
            'tanggal_selesai' => '2026-11-11 08:00:00'
        ]);

        PesananLaundry::create([
            'pelanggan_id' => 1,
            'jenis_layanan' => 'Cuci Setrika',
            'berat_kg' => 5.00,
            'total_harga' => 30000,
            'status_pesanan' => 'proses',
            'tanggal_masuk' => '2026-11-12 09:00:00',
            'tanggal_selesai' => null
        ]);
    }
}
