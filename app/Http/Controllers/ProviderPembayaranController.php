<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProviderPembayaranResource;
use Illuminate\Http\Request;
use App\Models\ProviderPembayaran;
use App\Models\MetodePembayaran;
use App\Models\Venue;

class ProviderPembayaranController extends Controller
{
    /**
     * Ambil semua provider pembayaran berdasarkan venue tertentu
     */
    public function ambilProviderPembayaran($venue_id)
    {
        $venue = Venue::find($venue_id);

        if (!$venue) {
            return response()->json(['message' => 'Venue tidak ditemukan'], 404);
        }

        $providerPembayaran = ProviderPembayaran::with('metodePembayaran')->where('venue_id', $venue_id)->get();

        if ($providerPembayaran->isEmpty()) {
            return response()->json(['message' => 'Provider tidak ditemukan untuk venue ini'], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => ProviderPembayaranResource::collection($providerPembayaran)
        ], 200);
    }

    /**
     * Buat provider pembayaran untuk venue tertentu
     */
    public function buatProviderPembayaran(Request $request, $venue_id)
    {
        $venue = Venue::find($venue_id);

        if (!$venue) {
            return response()->json(['message' => 'Venue tidak ditemukan'], 404);
        }

        // Cek apakah venue sudah memiliki provider
        if ($venue->provider) {
            return response()->json(['message' => 'Venue sudah memiliki provider pembayaran'], 400);
        }

        $request->validate([
            'nama' => 'required|string|unique:provider_pembayarans,nama|max:255',
            'no_rek' => 'required|string|max:50',
            'penerima' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:3000',
            'foto' => 'nullable|string|max:255',
            'aktif' => 'boolean',
            'metode_pembayaran_id' => 'required|exists:metode_pembayarans,id'
        ]);

        $providerPembayaran = ProviderPembayaran::create([
            'nama' => $request->nama,
            'no_rek' => $request->no_rek,
            'penerima' => $request->penerima,
            'deskripsi' => $request->deskripsi,
            'foto' => $request->foto,
            'aktif' => $request->aktif ?? true,
            'metode_pembayaran_id' => $request->metode_pembayaran_id,
            'venue_id' => $venue->id
        ]);

        return response()->json([
            'message' => 'Provider pembayaran berhasil dibuat',
            'data' => new ProviderPembayaranResource($providerPembayaran)
        ], 201);
    }

    /**
     * Ambil detail provider pembayaran berdasarkan ID dan Venue
     */
    public function detailProviderPembayaran($venue_id, $id)
    {
        $venue = Venue::find($venue_id);

        if (!$venue) {
            return response()->json(['message' => 'Venue tidak ditemukan'], 404);
        }

        $providerPembayaran = ProviderPembayaran::with('metodePembayaran')
            ->where('venue_id', $venue_id)
            ->where('id', $id)
            ->first();

        if (!$providerPembayaran) {
            return response()->json(['message' => 'Provider tidak ditemukan untuk venue ini'], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diambil',
            'data' => new ProviderPembayaranResource($providerPembayaran)
        ], 200);
    }

    /**
     * Ubah data provider pembayaran untuk venue tertentu
     */
    public function ubahProviderPembayaran(Request $request, $venue_id, $id)
    {
        $venue = Venue::find($venue_id);

        if (!$venue) {
            return response()->json(['message' => 'Venue tidak ditemukan'], 404);
        }

        $providerPembayaran = ProviderPembayaran::where('venue_id', $venue_id)->find($id);

        if (!$providerPembayaran) {
            return response()->json(['message' => 'Provider tidak ditemukan untuk venue ini'], 404);
        }

        $request->validate([
            'nama' => 'sometimes|string|max:255|unique:provider_pembayarans,nama,' . $id,
            'no_rek' => 'sometimes|string|max:50',
            'penerima' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string|max:3000',
            'foto' => 'nullable|string|max:255',
            'aktif' => 'boolean',
            'metode_pembayaran_id' => 'required|exists:metode_pembayarans,id'
        ]);

        $providerPembayaran->update($request->only([
            'nama', 'no_rek', 'penerima', 'deskripsi', 'foto', 'aktif', 'metode_pembayaran_id'
        ]));

        return response()->json([
            'message' => 'Provider pembayaran berhasil diperbarui',
            'data' => new ProviderPembayaranResource($providerPembayaran)
        ], 200);
    }

    /**
     * Ubah status aktif/inaktif provider pembayaran
     */
    public function ubahStatusProviderPembayaran($venue_id, $id)
    {
        $venue = Venue::find($venue_id);

        if (!$venue) {
            return response()->json(['message' => 'Venue tidak ditemukan'], 404);
        }

        $providerPembayaran = ProviderPembayaran::where('venue_id', $venue_id)->find($id);

        if (!$providerPembayaran) {
            return response()->json(['message' => 'Provider tidak ditemukan untuk venue ini'], 404);
        }

        // Toggle status aktif
        $providerPembayaran->aktif = !$providerPembayaran->aktif;
        $providerPembayaran->save();

        return response()->json([
            'message' => 'Status provider pembayaran berhasil diperbarui',
            'data' => new ProviderPembayaranResource($providerPembayaran)
        ]);
    }

    /**
     * Hapus provider pembayaran untuk venue tertentu
     */
    public function hapusProviderPembayaran($venue_id, $id)
    {
        $venue = Venue::find($venue_id);

        if (!$venue) {
            return response()->json(['message' => 'Venue tidak ditemukan'], 404);
        }

        $providerPembayaran = ProviderPembayaran::where('venue_id', $venue_id)->find($id);

        if (!$providerPembayaran) {
            return response()->json(['message' => 'Provider tidak ditemukan untuk venue ini'], 404);
        }

        $providerPembayaran->delete();

        return response()->json([
            'message' => 'Provider pembayaran berhasil dihapus'
        ]);
    }
}
