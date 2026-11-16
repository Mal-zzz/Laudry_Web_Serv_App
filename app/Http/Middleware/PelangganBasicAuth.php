<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Pelanggan; // Pastikan memanggil model Pelanggan
use Illuminate\Support\Facades\Hash;

class PelangganBasicAuth
{
    public function handle($request, Closure $next)
    {
        // 1. Ambil username (email) dan password dari Header Basic Auth bawaan Lumen/PHP
        $email = $request->getUser();
        $password = $request->getPassword();

        // 2. Jika header kosong / tidak ada
        if (!$email || !$password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Header Basic Auth (email dan password) diperlukan.'
            ], 401, ['WWW-Authenticate' => 'Basic']);
        }

        // 3. Cari data pelanggan berdasarkan email di database
        $pelanggan = Pelanggan::where('email', $email)->first();

        // 4. Cek apakah pelanggan ditemukan dan verifikasi passwordnya (hash-nya cocok)
        if (!$pelanggan || !Hash::check($password, $pelanggan->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Email atau password salah.'
            ], 401, ['WWW-Authenticate' => 'Basic']);
        }

        // 5. Jika valid, izinkan request lanjut ke Controller
        return $next($request);
    }
}
