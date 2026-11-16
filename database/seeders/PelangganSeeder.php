<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelanggan;

class PelangganSeeder extends Seeder
{
    public function run()
    {
        Pelanggan::create([
            'kode_pelanggan' => 'PLG001',
            'nama' => 'Andi Saputra',
            'jenis_kelamin' => 'L',
            'alamat' => 'Bandung',
            'no_hp' => '081234567801',
            'email' => 'andi@gmail.com',
            'tanggal_lahir' => '2000-01-10',
            'pekerjaan' => 'Mahasiswa',
            'tanggal_daftar' => '2026-11-01',
            'status' => 'aktif'
        ]);

        Pelanggan::create([
            'kode_pelanggan' => 'PLG002',
            'nama' => 'Siti Aminah',
            'jenis_kelamin' => 'P',
            'alamat' => 'Cimahi',
            'no_hp' => '081234567802',
            'email' => 'siti@gmail.com',
            'tanggal_lahir' => '2001-03-15',
            'pekerjaan' => 'Guru',
            'tanggal_daftar' => '2026-11-02',
            'status' => 'aktif'
        ]);
    }
}
