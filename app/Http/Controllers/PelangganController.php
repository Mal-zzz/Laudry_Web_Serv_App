<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    // GET /pelanggan
    public function index(Request $request)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        return $this->respond(Pelanggan::all());
    }

    // GET /pelanggan/{id}
    public function show(Request $request, $id)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        return $this->respond(Pelanggan::findOrFail($id));
    }

    // POST /pelanggan
    public function store(Request $request)
    {
        if ($error = $this->checkAccept($request) ?? $this->checkContentType($request)) {
            return $error;
        }

        $data = $this->validate($request, [
            'kode_pelanggan' => 'required|unique:pelanggan,kode_pelanggan',
            'nama'           => 'required',
            'jenis_kelamin'  => 'required|in:L,P',
            'alamat'         => 'required',
            'no_hp'          => 'required',
            'email'          => 'required|email|unique:pelanggan,email',
            'tanggal_lahir'  => 'nullable|date',
            'pekerjaan'      => 'nullable',
            'tanggal_daftar' => 'required|date',
            'status'         => 'nullable',
        ]);

        return $this->respond(Pelanggan::create($data), 201);
    }

    // PUT /pelanggan/{id}
    public function update(Request $request, $id)
    {
        if ($error = $this->checkAccept($request) ?? $this->checkContentType($request)) {
            return $error;
        }

        $pelanggan = Pelanggan::findOrFail($id);

        $data = $this->validate($request, [
            'kode_pelanggan' => 'required|unique:pelanggan,kode_pelanggan,' . $id,
            'nama'           => 'required',
            'jenis_kelamin'  => 'required|in:L,P',
            'alamat'         => 'required',
            'no_hp'          => 'required',
            'email'          => 'required|email|unique:pelanggan,email,' . $id,
            'tanggal_lahir'  => 'nullable|date',
            'pekerjaan'      => 'nullable',
            'tanggal_daftar' => 'required|date',
            'status'         => 'nullable',
        ]);

        $pelanggan->update($data);

        return $this->respond($pelanggan);
    }

    // DELETE /pelanggan/{id}
    public function destroy(Request $request, $id)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        Pelanggan::findOrFail($id)->delete();

        return $this->respond(['message' => 'Pelanggan berhasil dihapus']);
    }






    /* ---------- Helper ---------- */

    // Semua respons dikirim sebagai JSON dengan header yang jelas
    private function respond($data, $status = 200)
    {
        return response()->json($data, $status, [
            'Content-Type' => 'application/json',
        ]);
    }

    // Klien harus menerima JSON (Accept: application/json atau */*)
    private function checkAccept(Request $request)
    {
        if (!str_contains($request->header('Accept', ''), 'application/json')) {
            return $this->respond(['message' => 'Header Accept harus application/json'], 406);
        }

        return null;
    }

    // Body POST/PUT harus JSON (Content-Type: application/json)
    private function checkContentType(Request $request)
    {
        if (!$request->isJson()) {
            return $this->respond(['message' => 'Header Content-Type harus application/json'], 415);
        }

        return null;
    }
}
