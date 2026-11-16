<?php

namespace App\Http\Controllers;

use App\Models\PesananLaundry;
use Illuminate\Http\Request;

class PesananLaundryController extends Controller
{
    // GET /api/pesanan-laundry
    public function index(Request $request)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        return $this->respond(PesananLaundry::with('pelanggan')->get());
    }

    // GET /api/pesanan-laundry/{id}
    public function show(Request $request, $id)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        return $this->respond(PesananLaundry::with('pelanggan')->findOrFail($id));
    }

    // POST /api/pesanan-laundry
    public function store(Request $request)
    {
        if ($error = $this->checkAccept($request) ?? $this->checkContentType($request)) {
            return $error;
        }

        $data = $this->validate($request, [
            'pelanggan_id'    => 'required|integer|exists:pelanggan,id',
            'jenis_layanan'   => 'required|string|max:50',
            'berat_kg'        => 'required|numeric|min:0.01',
            'total_harga'     => 'required|numeric|min:0',
            'status_pesanan'  => 'nullable|string|max:30',
            'tanggal_masuk'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_masuk',
        ]);

        $pesanan = PesananLaundry::create($data);

        return $this->respond($pesanan->load('pelanggan'), 201);
    }

    // PUT /api/pesanan-laundry/{id}
    public function update(Request $request, $id)
    {
        if ($error = $this->checkAccept($request) ?? $this->checkContentType($request)) {
            return $error;
        }

        $pesanan = PesananLaundry::findOrFail($id);

        $data = $this->validate($request, [
            'pelanggan_id'    => 'required|integer|exists:pelanggan,id',
            'jenis_layanan'   => 'required|string|max:50',
            'berat_kg'        => 'required|numeric|min:0.01',
            'total_harga'     => 'required|numeric|min:0',
            'status_pesanan'  => 'nullable|string|max:30',
            'tanggal_masuk'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_masuk',
        ]);

        $pesanan->update($data);

        return $this->respond($pesanan->load('pelanggan'));
    }

    // DELETE /api/pesanan-laundry/{id}
    public function destroy(Request $request, $id)
    {
        if ($error = $this->checkAccept($request)) {
            return $error;
        }

        PesananLaundry::findOrFail($id)->delete();

        return $this->respond(['message' => 'Pesanan laundry berhasil dihapus']);
    }

    /* ---------- Helper ---------- */

    private function respond($data, $status = 200)
    {
        return response()->json($data, $status, [
            'Content-Type' => 'application/json',
        ]);
    }

    private function checkAccept(Request $request)
    {
        if (!str_contains($request->header('Accept', ''), 'application/json')) {
            return $this->respond(['message' => 'Header Accept harus application/json'], 406);
        }

        return null;
    }

    private function checkContentType(Request $request)
    {
        if (!$request->isJson()) {
            return $this->respond(['message' => 'Header Content-Type harus application/json'], 415);
        }

        return null;
    }
}
