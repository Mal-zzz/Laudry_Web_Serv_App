<?php

namespace App\Http\Controllers;

use App\Models\PesananLaundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\AcceptHeader;
// use Illuminate\Support\Facades\Auth;

class PesananLaundryController extends Controller
{
    /**
     * Helper agar tidak duplikasi kode & aman dari karakter khusus (&, <, >)
     */
    private function appendPesananLaundryToXml(\SimpleXMLElement $xml, $pesanan)
    {
        $xml->id              = $pesanan->id;
        $xml->pelanggan_id    = $pesanan->pelanggan_id;
        $xml->jenis_layanan   = $pesanan->jenis_layanan;
        $xml->berat_kg        = $pesanan->berat_kg;
        $xml->total_harga     = $pesanan->total_harga;
        $xml->status_pesanan  = $pesanan->status_pesanan;
        $xml->tanggal_masuk   = $pesanan->tanggal_masuk;
        $xml->tanggal_selesai = $pesanan->tanggal_selesai ?? '';
        $xml->created_at      = (string) $pesanan->created_at;
        $xml->updated_at      = (string) ($pesanan->updated_at ?? '');
    }


    /**
     * MENAMPILKAN DATA (Bisa semua data dengan pagination, atau detail berdasarkan ID)
     */
    public function getPesananLaundry(Request $request, $id = null)
    {
        $acceptHeader = $request->header('Accept', '');

        $isJson = str_contains($acceptHeader, 'application/json');
        $isXml  = str_contains($acceptHeader, 'application/xml');

        if (!$isJson && !$isXml) {
            return response()->json(['message' => 'Not Acceptable!'], 406);
        }

        // 1. KONDISI JIKA $id DIISI
        if ($id != null) {
            $pesanan = PesananLaundry::find($id);

            if (!$pesanan) {
                return response()->json(['message' => 'Pesanan laundry not found'], 404);
            }

            if ($isJson) {
                return response()->json($pesanan, 200);
            }

            $xml = new \SimpleXMLElement('<pesanan_laundry/>');
            $this->appendPesananLaundryToXml($xml, $pesanan);

            return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
        }

        // 2. KONDISI JIKA $id KOSONG (Biarkan sebagai objek Paginator, jangan langsung ->toArray())
        $paginator = PesananLaundry::orderBy("id", "DESC")->paginate(2);

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
        $xml = new \SimpleXMLElement('<data_pesanan_laundry/>');
        $xml->addChild('total_count', $paginator->total());
        $xml->addChild('limit', $paginator->perPage());
        $xml->addChild('current_page', $paginator->currentPage());

        foreach ($paginator->items() as $item) {
            $xmlItem = $xml->addChild('pesanan_laundry');
            $this->appendPesananLaundryToXml($xmlItem, $item);
        }

        return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
    }


    /**
     * MENAMBAH DATA BARU
     */
    public function storePesananLaundry(Request $request)
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

        // 3. Validasi input sesuai field PesananLaundry
        $validationRules = [
            'pelanggan_id'    => 'required|exists:pelanggan,id',
            'jenis_layanan'   => 'required',
            'berat_kg'        => 'required|numeric',
            'total_harga'     => 'required|numeric',
            'status_pesanan'  => 'required',
            'tanggal_masuk'   => 'required|date',
            'tanggal_selesai' => 'nullable|date',
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
        $pesanan = PesananLaundry::create($input);

        // 5. Kembalikan response sesuai header Accept (status 201 Created)
        if ($isJson) {
            return response()->json($pesanan, 201);
        }

        $xml = new \SimpleXMLElement('<pesanan_laundry/>');
        $this->appendPesananLaundryToXml($xml, $pesanan);

        return response($xml->asXml(), 201)->header('Content-Type', 'application/xml');
    }


    /**
     * MENGUBAH DATA BERDASARKAN ID
     */
    public function updatePesananLaundry(Request $request, $id)
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

        // 3. Cek apakah data pesanan laundry ada
        $pesanan = PesananLaundry::find($id);
        if (!$pesanan) {
            if ($isJson) {
                return response()->json(['message' => 'Pesanan laundry not found'], 404);
            }

            $xmlNotFound = new \SimpleXMLElement('<error/>');
            $xmlNotFound->message = 'Pesanan laundry not found';
            return response($xmlNotFound->asXml(), 404)->header('Content-Type', 'application/xml');
        }

        $inputs = $request->all();

        // 4. Validasi input
        $validationRules = [
            'pelanggan_id'    => 'required|exists:pelanggan,id',
            'jenis_layanan'   => 'required',
            'berat_kg'        => 'required|numeric',
            'total_harga'     => 'required|numeric',
            'status_pesanan'  => 'required',
            'tanggal_masuk'   => 'required|date',
            'tanggal_selesai' => 'nullable|date',
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
        $pesanan->fill($inputs);
        $pesanan->save();

        // 6. Kembalikan response sesuai header Accept
        if ($isJson) {
            return response()->json($pesanan, 200);
        }

        $xml = new \SimpleXMLElement('<pesanan_laundry/>');
        $this->appendPesananLaundryToXml($xml, $pesanan);

        return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
    }


    /**
     * MENGHAPUS DATA BERDASARKAN ID
     */
    public function destroyPesananLaundry(Request $request, $id)
    {
        $acceptHeader = $request->header('Accept', '');
        $isJson = str_contains($acceptHeader, 'application/json');
        $isXml  = str_contains($acceptHeader, 'application/xml');

        // 1. Validasi header Accept
        if (!$isJson && !$isXml) {
            return response()->json(['message' => 'Not Acceptable!'], 406);
        }

        // 2. Cek apakah data pesanan laundry tersedia
        $pesanan = PesananLaundry::find($id);
        if (!$pesanan) {
            if ($isJson) {
                return response()->json(['message' => 'Pesanan laundry not found'], 404);
            }

            $xmlNotFound = new \SimpleXMLElement('<error/>');
            $xmlNotFound->message = 'Pesanan laundry not found';
            return response($xmlNotFound->asXml(), 404)->header('Content-Type', 'application/xml');
        }

        // 3. Eksekusi hapus data
        $pesanan->delete();

        // 4. Kembalikan response sesuai header Accept
        if ($isJson) {
            return response()->json([
                'message'            => 'delete successfully',
                'pesanan_laundry_id' => $id
            ], 200);
        }

        $xml = new \SimpleXMLElement('<response/>');
        $xml->message            = 'delete successfully';
        $xml->pesanan_laundry_id = $id;

        return response($xml->asXml(), 200)->header('Content-Type', 'application/xml');
    }
}
