<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;

class AdminHardcodeAuth
{
    public function handle($request, Closure $next)
    {
        // 1. Ambil email dan password dari header Basic Auth Postman
        $email = $request->getUser();
        $password = $request->getPassword();

        // 2. Tentukan email dan password admin langsung di sini (bisa juga ditaruh di file .env)
        $adminEmail = 'admin@gmail.com'; 
        $adminPasswordHash = Hash::make('admin123'); 

        // 3. Validasi pencocokan
        if ($email !== $adminEmail || !Hash::check($password, $adminPasswordHash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Hanya admin yang berhak mengakses aksi ini.'
            ], 401, ['WWW-Authenticate' => 'Basic']);
        }

        // 4. Jika benar, lanjut ke controller
        return $next($request);
    }
}