<?php

namespace App\Http\Controllers;

use App\Models\SpecRabDetail;
use App\Models\SpesifikasiRAB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecRabDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SpecRabDetail::with('spesifikasiRab');

        // Search handle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id_spec', 'LIKE', "%{$search}%")
                  ->orWhere('cost_element', 'LIKE', "%{$search}%")
                  ->orWhere('description_ce', 'LIKE', "%{$search}%")
                  ->orWhereHas('spesifikasiRab', function($sq) use ($search) {
                      $sq->where('spec_rab', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Ordering - now just plain ordering
        $details = $query->orderBy('id_spec', 'asc')
                         ->orderBy('cost_element', 'asc')
                         ->paginate($request->get('per_page', 10));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $details->items(),
                'pagination' => [
                    'current_page' => $details->currentPage(),
                    'last_page' => $details->lastPage(),
                    'per_page' => $details->perPage(),
                    'total' => $details->total(),
                    'from' => $details->firstItem(),
                    'to' => $details->lastItem()
                ]
            ]);
        }

        return view('detail_rab.index', compact('details'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_spec' => 'required|string|max:10|exists:spec_rab,id_spec',
            'cost_element' => 'required|string|max:10|unique:spec_rab_detail,cost_element',
            'description_ce' => 'nullable|string',
            'status' => 'nullable|in:A,N',
        ], [
            'id_spec.required' => 'ID Spec harus dipilih.',
            'id_spec.exists' => 'ID Spec tidak valid.',
            'cost_element.required' => 'Cost Element harus diisi.',
            'cost_element.max' => 'Cost Element maksimal 10 karakter.',
            'cost_element.unique' => 'Cost Element sudah terdaftar.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            // Set default status jika tidak ada
            if (empty($validated['status'])) {
                $validated['status'] = 'A';
            }

            $detail = SpecRabDetail::create($validated);
            $detail->load('spesifikasiRab');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan.',
                'data' => $detail
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $cost_element)
    {
        $detail = SpecRabDetail::with('spesifikasiRab')->find($cost_element);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detail
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Note: cost_element tidak boleh diubah (karena PK), tapi id_spec bisa diubah (pindah grup)
     */
    public function update(Request $request, string $cost_element)
    {
        $detail = SpecRabDetail::find($cost_element);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validate([
            'id_spec' => 'required|string|max:10|exists:spec_rab,id_spec',
            'description_ce' => 'nullable|string',
            'status' => 'nullable|in:A,N',
        ], [
            'id_spec.required' => 'ID Spec harus dipilih.',
            'id_spec.exists' => 'ID Spec tidak valid.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            $detail->update([
                'id_spec' => $validated['id_spec'],
                'description_ce' => $validated['description_ce'] ?? $detail->description_ce,
                'status' => $validated['status'] ?? $detail->status,
            ]);

            // Refresh data
            $detail->load('spesifikasiRab');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
                'data' => $detail
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cost_element)
    {
        $detail = SpecRabDetail::find($cost_element);

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $detail->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    /**
     * Get active spesifikasi RAB untuk dropdown
     */
    public function getActiveSpecs()
    {
        try {
            $specs = SpesifikasiRAB::active()
                                   ->ordered()
                                   ->get(['id_spec', 'spec_rab', 'kategori']);

            return response()->json([
                'success' => true,
                'data' => $specs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.'
            ], 500);
        }
    }
}
