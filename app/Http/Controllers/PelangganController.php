<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\AcceptHeader;
// use illuminate\Support\Facades\Auth;

class PelangganController extends Controller
{
    /**
     * Helper agar tidak duplikasi kode & aman dari karakter khusus (&, <, >)
     */
    private function appendPelangganToXml(\SimpleXMLElement $xml, $pelanggan)
    {
        $xml->id             = $pelanggan->id;
        $xml->kode_pelanggan = $pelanggan->kode_pelanggan;
        $xml->nama           = $pelanggan->nama;
        $xml->jenis_kelamin  = $pelanggan->jenis_kelamin;
        $xml->alamat         = $pelanggan->alamat;
        $xml->no_hp          = $pelanggan->no_hp;
        $xml->email          = $pelanggan->email;
        $xml->tanggal_lahir  = $pelanggan->tanggal_lahir;
        $xml->pekerjaan      = $pelanggan->pekerjaan;
        $xml->tanggal_daftar = $pelanggan->tanggal_daftar;
        $xml->status         = $pelanggan->status;
        $xml->created_at     = (string) $pelanggan->created_at;
        $xml->updated_at     = (string) ($pelanggan->updated_at ?? '');
    }





    /**
     * MENAMPILKAN DATA (Bisa semua data dengan pagination, atau detail berdasarkan ID)
     */
    public function getPelanggan(Request $request, $id = null)
    {
        $acceptHeader = $request->header('Accept', '');

        $isJson = str_contains($acceptHeader, 'application/json');
        $isXml  = str_contains($acceptHeader, 'application/xml');

        if (!$isJson && !$isXml) {
            return response()->json(['message' => 'Not Acceptable!'], 406);
        }

        // 1. KONDISI JIKA $id DIISI
        if ($id != null) {
            $pelanggan = Pelanggan::find($id);

            if (!$pelanggan) {
                return response()->json(['message' => 'Pelanggan not found'], 404);
            }

            if ($isJson) {
                return response()->json($pelanggan, 200);
            }

            $xml = new \SimpleXMLElement('<pelanggan/>');
            $this->appendPelangganToXml($xml, $pelanggan);

            return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
        }

        // 2. KONDISI JIKA $id KOSONG (Biarkan sebagai objek Paginator, jangan langsung ->toArray())
        $paginator = Pelanggan::orderBy("id", "DESC")->paginate(2);

        if ($isJson) {
            $dataArray = $paginator->toArray();
            $response = [
                "total_count" => $dataArray['total'],
                "limit"       => $dataArray['per_page'],
                "pagination"  => [
                    "next_page"    => $dataArray['next_page_url'],
                    "current_page" => $dataArray['current_page']
                ],
                "data"        => $dataArray['data']
            ];

            return response()->json($response, 200);
        }

        // Jika format yang diminta adalah XML
        $xml = new \SimpleXMLElement('<data_pelanggan/>');
        $xml->addChild('total_count', $paginator->total());
        $xml->addChild('limit', $paginator->perPage());
        $xml->addChild('current_page', $paginator->currentPage());

        foreach ($paginator->items() as $item) {
            $xmlItem = $xml->addChild('pelanggan');
            $this->appendPelangganToXml($xmlItem, $item);
        }

        return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
    }



    /**
     * MENAMBAH DATA BARU
     */
    public function storePelanggan(Request $request)
    {
        $acceptHeader = $request->header('Accept', '');
        $isJson = str_contains($acceptHeader, 'application/json');
        $isXml  = str_contains($acceptHeader, 'application/xml');

        // 1. Validasi header Accept
        if (!$isJson && !$isXml) {
            return response()->json(['message' => 'Not Acceptable!'], 406);
        }

        // 2. Validasi header Content-Type (wajib kirim JSON di body)
        $contentTypeHeader = $request->header('Content-Type', '');
        if (!str_contains($contentTypeHeader, 'application/json')) {
            return response()->json(['message' => 'Unsupported Media Type'], 415);
        }

        $input = $request->all();

        // 3. Validasi input sesuai field Pelanggan
        $validationRules = [
            'kode_pelanggan' => 'required|unique:pelanggans,kode_pelanggan',
            'nama'           => 'required|min:3',
            'jenis_kelamin'  => 'required|in:L,P',
            'alamat'         => 'required',
            'no_hp'          => 'required',
            'email'          => 'required|email|unique:pelanggans,email',
            'tanggal_lahir'  => 'required|date',
            'pekerjaan'      => 'required',
            'tanggal_daftar' => 'required|date',
            'status'         => 'required|in:aktif,nonaktif',
        ];

        $validator = Validator::make($input, $validationRules);

        if ($validator->fails()) {
            if ($isJson) {
                return response()->json($validator->errors(), 400);
            }

            // Response error validasi dalam bentuk XML
            $xmlError = new \SimpleXMLElement('<errors/>');
            foreach ($validator->errors()->all() as $err) {
                $xmlError->addChild('error', htmlspecialchars($err));
            }
            return response($xmlError->asXml(), 400)->header('Content-Type', 'application/xml');
        }

        // 4. Simpan ke database
        $pelanggan = Pelanggan::create($input);

        // 5. Kembalikan response sesuai header Accept (status 201 Created)
        if ($isJson) {
            return response()->json($pelanggan, 201);
        }

        $xml = new \SimpleXMLElement('<pelanggan/>');
        $this->appendPelangganToXml($xml, $pelanggan);

        return response($xml->asXml(), 201)->header('Content-Type', 'application/xml');
    }







    /**
     * MENGUBAH DATA BERDASARKAN ID
     */
    public function updatePelanggan(Request $request, $id)
    {
        $acceptHeader = $request->header('Accept', '');
        $isJson = str_contains($acceptHeader, 'application/json');
        $isXml  = str_contains($acceptHeader, 'application/xml');

        // 1. Validasi header Accept
        if (!$isJson && !$isXml) {
            return response()->json(['message' => 'Not Acceptable!'], 406);
        }

        // 2. Validasi header Content-Type (body wajib JSON)
        $contentTypeHeader = $request->header('Content-Type', '');
        if (!str_contains($contentTypeHeader, 'application/json')) {
            return response()->json(['message' => 'Unsupported Media Type'], 415);
        }

        // 3. Cek apakah data pelanggan ada
        $pelanggan = Pelanggan::find($id);
        if (!$pelanggan) {
            if ($isJson) {
                return response()->json(['message' => 'Pelanggan not found'], 404);
            }

            $xmlNotFound = new \SimpleXMLElement('<error/>');
            $xmlNotFound->message = 'Pelanggan not found';
            return response($xmlNotFound->asXml(), 404)->header('Content-Type', 'application/xml');
        }

        $inputs = $request->all();

        // 4. Validasi input (kecualikan $id saat ini pada aturan unique)
        $validationRules = [
            'kode_pelanggan' => 'required|unique:pelanggans,kode_pelanggan,' . $id,
            'nama'           => 'required|min:3',
            'jenis_kelamin'  => 'required|in:L,P',
            'alamat'         => 'required',
            'no_hp'          => 'required',
            'email'          => 'required|email|unique:pelanggans,email,' . $id,
            'tanggal_lahir'  => 'required|date',
            'pekerjaan'      => 'required',
            'tanggal_daftar' => 'required|date',
            'status'         => 'required|in:aktif,nonaktif',
        ];

        $validator = Validator::make($inputs, $validationRules);

        if ($validator->fails()) {
            if ($isJson) {
                return response()->json($validator->errors(), 400);
            }

            $xmlError = new \SimpleXMLElement('<errors/>');
            foreach ($validator->errors()->all() as $err) {
                $xmlError->addChild('error', htmlspecialchars($err));
            }
            return response($xmlError->asXml(), 400)->header('Content-Type', 'application/xml');
        }

        // 5. Update data ke database
        $pelanggan->fill($inputs);
        $pelanggan->save();

        // 6. Kembalikan response sesuai header Accept
        if ($isJson) {
            return response()->json($pelanggan, 200);
        }

        $xml = new \SimpleXMLElement('<pelanggan/>');
        $this->appendPelangganToXml($xml, $pelanggan);

        return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
    }








    /**
     * MENGHAPUS DATA BERDASARKAN ID
     */
    public function destroyPelanggan(Request $request, $id)
    {
        $acceptHeader = $request->header('Accept', '');
        $isJson = str_contains($acceptHeader, 'application/json');
        $isXml  = str_contains($acceptHeader, 'application/xml');

        // 1. Validasi header Accept
        if (!$isJson && !$isXml) {
            return response()->json(['message' => 'Not Acceptable!'], 406);
        }

        // 2. Cek apakah data pelanggan tersedia
        $pelanggan = Pelanggan::find($id);
        if (!$pelanggan) {
            if ($isJson) {
                return response()->json(['message' => 'Pelanggan not found'], 404);
            }

            $xmlNotFound = new \SimpleXMLElement('<error/>');
            $xmlNotFound->message = 'Pelanggan not found';
            return response($xmlNotFound->asXml(), 404)->header('Content-Type', 'application/xml');
        }

        // 3. Eksekusi hapus data
        $pelanggan->delete();

        // 4. Kembalikan response sesuai header Accept
        if ($isJson) {
            return response()->json([
                'message'      => 'delete successfully',
                'pelanggan_id' => $id
            ], 200);
        }

        $xml = new \SimpleXMLElement('<response/>');
        $xml->message      = 'delete successfully';
        $xml->pelanggan_id = $id;

        return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
    }
}
