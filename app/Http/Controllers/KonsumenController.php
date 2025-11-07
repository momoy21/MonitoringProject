<?php

namespace App\Http\Controllers;

use App\Models\Konsumen;
use App\Models\Provinsi;
use App\Models\Kota;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
class KonsumenController extends Controller
{
    public function index(Request $request)
    {
        $query = Konsumen::with(['provinsi', 'kota']);

        // Search handle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('konsumen', 'LIKE', "%{$search}%");
                //   ->orWhere('id_konsumen', 'LIKE', "%{$search}%")
                //   ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $konsumen = $query->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 10));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $konsumen->items(),
                'pagination' => [
                    'current_page' => $konsumen->currentPage(),
                    'last_page' => $konsumen->lastPage(),
                    'per_page' => $konsumen->perPage(),
                    'total' => $konsumen->total(),
                    'from' => $konsumen->firstItem(),
                    'to' => $konsumen->lastItem(),
                ]
            ]);
        }

        return view('konsumen.index', compact('konsumen'));
    }

    public function create()
    {
        $provinsi = Provinsi::orderBy('nama')->get();
        return view('konsumen.create', compact('provinsi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'konsumen' => 'required|string|max:150',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kota_id' => 'nullable|exists:kota,id',
            'alamat1' => 'nullable|string|max:255',
            'alamat2' => 'nullable|string|max:255',
            'kode_pos' => 'nullable|string|max:5',
            'telp_kantor' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:70|unique:konsumen,email',
            'status' => 'nullable|in:A,N',
        ], [
            'konsumen.required' => 'Nama konsumen harus diisi.',
            'konsumen.max' => 'Nama konsumen maksimal 150 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kota_id.exists' => 'Kota yang dipilih tidak valid.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            $konsumen = Konsumen::create($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Konsumen berhasil ditambahkan.',
                    'data' => $konsumen->load(['provinsi', 'kota'])
                ]);
            }

            return redirect()->route('konsumen.index')
                           ->with('success', 'Konsumen berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data.',
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                           ->withInput();
        }
    }

    public function show(Konsumen $konsumen)
    {
        $konsumen->load(['provinsi', 'kota']);

        return response()->json([
            'success' => true,
            'data' => $konsumen
        ]);
    }

    public function edit(Konsumen $konsumen)
    {
        $provinsi = Provinsi::orderBy('nama')->get();
        $kota = collect();

        if ($konsumen->provinsi_id) {
            $kota = Kota::where('provinsi_id', $konsumen->provinsi_id)
                       ->orderBy('nama')
                       ->get();
        }

        return view('konsumen.edit', compact('konsumen', 'provinsi', 'kota'));
    }

    public function update(Request $request, Konsumen $konsumen)
    {
        $validated = $request->validate([
            'konsumen' => 'required|string|max:150',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kota_id' => 'nullable|exists:kota,id',
            'alamat1' => 'nullable|string|max:255',
            'alamat2' => 'nullable|string|max:255',
            'kode_pos' => 'nullable|string|max:5',
            'telp_kantor' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => [
                'nullable',
                'email',
                'max:70',
                Rule::unique('konsumen', 'email')->ignore($konsumen->id_konsumen, 'id_konsumen')
            ],
            'status' => 'nullable|in:A,N',
        ], [
            'konsumen.required' => 'Nama konsumen harus diisi.',
            'konsumen.max' => 'Nama konsumen maksimal 150 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'provinsi_id.exists' => 'Provinsi yang dipilih tidak valid.',
            'kota_id.exists' => 'Kota yang dipilih tidak valid.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            $konsumen->update($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Konsumen berhasil diperbarui.',
                    'data' => $konsumen->fresh(['provinsi', 'kota'])
                ]);
            }

            return redirect()->route('konsumen.index')
                           ->with('success', 'Konsumen berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data.',
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat memperbarui data.')
                           ->withInput();
        }
    }

    public function destroy(Konsumen $konsumen)
    {
        try {
            DB::beginTransaction();

            $konsumenNama = $konsumen->konsumen;
            $konsumen->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Konsumen {$konsumenNama} berhasil dihapus."
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.',
            ], 500);
        }
    }

    // API Routes
    public function getCities(Request $request)
    {
        $provinceId = $request->get('province_id');

        if (!$provinceId) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $cities = Kota::where('provinsi_id', $provinceId)
                     ->orderBy('nama')
                     ->get(['id', 'nama', 'kode_pos']);

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function getAllCities()
    {
        $cities = Kota::with('provinsi')
                     ->orderBy('nama')
                     ->get(['id', 'nama', 'provinsi_id', 'kode_pos']);

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get active konsumen only (for use in other modules)
     */
    public function getActiveKonsumen(Request $request)
    {
        $query = Konsumen::active()->with(['provinsi', 'kota']);

        // Search handle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('konsumen', 'LIKE', "%{$search}%")
                  ->orWhere('id_konsumen', 'LIKE', "%{$search}%");
            });
        }

        $konsumen = $query->orderBy('konsumen')
                         ->get(['id_konsumen', 'konsumen', 'provinsi_id', 'kota_id']);

        return response()->json([
            'success' => true,
            'data' => $konsumen
        ]);
    }
}
