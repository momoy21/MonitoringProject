<?php

namespace App\Http\Controllers;

use App\Models\DataPeluang;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DataPeluangController extends Controller
{
    /**
     * Convert dd/mm/yyyy date format to Y-m-d for database storage
     */
    private function convertDateFormat($dateString)
    {
        if (!$dateString) return null;

        // Check if already in Y-m-d format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }

        // Convert from dd/mm/yyyy to Y-m-d
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            $dateParts = explode('/', $dateString);
            return $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
        }

        return $dateString;
    }

    /**
     * Y-m-d date format to dd/mm/yyyy for konsisten display
     */
    private function formatDateForDisplay($dateString)
    {
        if (!$dateString) return null;

        try {
            $date = \Carbon\Carbon::parse($dateString);
            return $date->format('d/m/Y');
        } catch (\Exception $e) {
            return $dateString;
        }
    }

    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = $request->get('per_page', 10);

            $dataPeluang = DataPeluang::with(['konsumen'])
                ->when($search, function ($query) use ($search) {
                    $query->search($search);
                })
                ->orderBy('id_datapeluang', 'desc')
                ->paginate($perPage);

            // For AJAX requests
            if ($request->ajax()) {
                // Format data for AJAX response
                $formattedData = $dataPeluang->getCollection()->map(function ($item) {
                    return [
                        'id_datapeluang' => $item->id_datapeluang,
                        'peluang' => $item->peluang,
                        'id_konsumen' => $item->id_konsumen,
                        'kontak_person' => $item->kontak_person,
                        'no_hp' => $item->no_hp,
                        'lokasi' => $item->lokasi,
                        'tgl_peluang' => $item->tgl_peluang,
                        'target_peluang' => $item->target_peluang,
                        'biaya_peluang' => $item->biaya_peluang,
                        'pagu_peluang' => $item->pagu_peluang,
                        'status' => $item->status,
                        'konsumen' => $item->konsumen,
                        // Include formatted attributes
                        'biaya_peluang_formatted' => $item->biaya_peluang_formatted,
                        'pagu_peluang_formatted' => $item->pagu_peluang_formatted,
                        'status_label' => $item->status_label,
                        'status_badge' => $item->status_badge,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => $formattedData,
                    'pagination' => [
                        'current_page' => $dataPeluang->currentPage(),
                        'last_page' => $dataPeluang->lastPage(),
                        'per_page' => $dataPeluang->perPage(),
                        'total' => $dataPeluang->total(),
                        'from' => $dataPeluang->firstItem(),
                        'to' => $dataPeluang->lastItem(),
                    ]
                ]);
            }

            return view('datapeluang.index', compact('dataPeluang'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data'
                ], 500);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan saat memuat data']);
        }
    }

    public function create()
    {
        $konsumen = Konsumen::active()
            ->select('id_konsumen', 'konsumen')
            ->orderBy('konsumen')
            ->get();

        return view('datapeluang.create', compact('konsumen'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'peluang' => 'required|string|max:65535',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'kontak_person' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:25',
            'lokasi' => 'nullable|string|max:100',
            'tgl_peluang' => 'required|date',
            'target_peluang' => 'required|date|after_or_equal:tgl_peluang',
            'biaya_peluang' => 'nullable|numeric|min:0|max:9999999999999999',
            'pagu_peluang' => 'nullable|numeric|min:0|max:9999999999999999',
            'status' => 'required|in:N,I,D,C'
        ], [
            'peluang.required' => 'Nama peluang harus diisi.',
            'peluang.max' => 'Nama peluang terlalu panjang.',
            'id_konsumen.required' => 'Konsumen harus dipilih.',
            'id_konsumen.exists' => 'Konsumen yang dipilih tidak valid.',
            'kontak_person.max' => 'Kontak person maksimal 100 karakter.',
            'no_hp.max' => 'Nomor HP maksimal 25 karakter.',
            'lokasi.max' => 'Lokasi maksimal 100 karakter.',
            'tgl_peluang.required' => 'Tanggal peluang harus diisi.',
            'tgl_peluang.date' => 'Format tanggal peluang tidak valid.',
            'target_peluang.required' => 'Target peluang harus diisi.',
            'target_peluang.date' => 'Format target peluang tidak valid.',
            'target_peluang.after_or_equal' => 'Target peluang tidak boleh sebelum tanggal peluang.',
            'biaya_peluang.numeric' => 'Biaya peluang harus berupa angka.',
            'biaya_peluang.min' => 'Biaya peluang tidak boleh negatif.',
            'biaya_peluang.max' => 'Biaya peluang terlalu besar (maksimal 16 digit).',
            'pagu_peluang.numeric' => 'Pagu peluang harus berupa angka.',
            'pagu_peluang.min' => 'Pagu peluang tidak boleh negatif.',
            'pagu_peluang.max' => 'Pagu peluang terlalu besar (maksimal 16 digit).',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Clean currency values
            $data = $request->all();
            if (isset($data['biaya_peluang']) && $data['biaya_peluang']) {
                $data['biaya_peluang'] = preg_replace('/[^\d]/', '', $data['biaya_peluang']);
            }
            if (isset($data['pagu_peluang']) && $data['pagu_peluang']) {
                $data['pagu_peluang'] = preg_replace('/[^\d]/', '', $data['pagu_peluang']);
            }

            $dataPeluang = DataPeluang::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data peluang berhasil disimpan.',
                'data' => $dataPeluang
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data peluang.'
            ], 500);
        }
    }

    public function show(Request $request, DataPeluang $datapeluang)
    {
        try {
            $datapeluang->load(['konsumen']);

            if ($request->ajax()) {
                // Format data for AJAX response
                $formattedData = [
                    'id_datapeluang' => $datapeluang->id_datapeluang,
                    'peluang' => $datapeluang->peluang,
                    'id_konsumen' => $datapeluang->id_konsumen,
                    'kontak_person' => $datapeluang->kontak_person,
                    'no_hp' => $datapeluang->no_hp,
                    'lokasi' => $datapeluang->lokasi,
                    'tgl_peluang' => $datapeluang->tgl_peluang,
                    'target_peluang' => $datapeluang->target_peluang,
                    'biaya_peluang' => $datapeluang->biaya_peluang,
                    'pagu_peluang' => $datapeluang->pagu_peluang,
                    'status' => $datapeluang->status,
                    'konsumen' => $datapeluang->konsumen,
                    // Include formatted attributes
                    'biaya_peluang_formatted' => $datapeluang->biaya_peluang_formatted,
                    'pagu_peluang_formatted' => $datapeluang->pagu_peluang_formatted,
                    'status_label' => $datapeluang->status_label,
                    'status_badge' => $datapeluang->status_badge,
                ];

                return response()->json([
                    'success' => true,
                    'data' => $formattedData
                ]);
            }

            return view('datapeluang.show', compact('datapeluang'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat detail data peluang'
                ], 500);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan saat memuat detail data peluang']);
        }
    }

    public function edit(DataPeluang $datapeluang)
    {
        $konsumen = Konsumen::active()
            ->select('id_konsumen', 'konsumen')
            ->orderBy('konsumen')
            ->get();

        return view('datapeluang.edit', compact('datapeluang', 'konsumen'));
    }

    public function update(Request $request, DataPeluang $datapeluang)
    {
        $validator = Validator::make($request->all(), [
            'peluang' => 'required|string|max:65535',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'kontak_person' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:25',
            'lokasi' => 'nullable|string|max:100',
            'tgl_peluang' => 'required|date',
            'target_peluang' => 'required|date|after_or_equal:tgl_peluang',
            'biaya_peluang' => 'nullable|numeric|min:0|max:9999999999999999',
            'pagu_peluang' => 'nullable|numeric|min:0|max:9999999999999999',
            'status' => 'required|in:N,I,D,C'
        ], [
            'peluang.required' => 'Nama peluang harus diisi.',
            'peluang.max' => 'Nama peluang terlalu panjang.',
            'id_konsumen.required' => 'Konsumen harus dipilih.',
            'id_konsumen.exists' => 'Konsumen yang dipilih tidak valid.',
            'kontak_person.max' => 'Kontak person maksimal 100 karakter.',
            'no_hp.max' => 'Nomor HP maksimal 25 karakter.',
            'lokasi.max' => 'Lokasi maksimal 100 karakter.',
            'tgl_peluang.required' => 'Tanggal peluang harus diisi.',
            'tgl_peluang.date' => 'Format tanggal peluang tidak valid.',
            'target_peluang.required' => 'Target peluang harus diisi.',
            'target_peluang.date' => 'Format target peluang tidak valid.',
            'target_peluang.after_or_equal' => 'Target peluang tidak boleh sebelum tanggal peluang.',
            'biaya_peluang.numeric' => 'Biaya peluang harus berupa angka.',
            'biaya_peluang.min' => 'Biaya peluang tidak boleh negatif.',
            'biaya_peluang.max' => 'Biaya peluang terlalu besar (maksimal 16 digit).',
            'pagu_peluang.numeric' => 'Pagu peluang harus berupa angka.',
            'pagu_peluang.min' => 'Pagu peluang tidak boleh negatif.',
            'pagu_peluang.max' => 'Pagu peluang terlalu besar (maksimal 16 digit).',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Clean currency values and convert date format
            $data = $request->all();

            // Convert dates from dd/mm/yyyy to Y-m-d
            if (isset($data['tgl_peluang'])) {
                $data['tgl_peluang'] = $this->convertDateFormat($data['tgl_peluang']);
            }
            if (isset($data['target_peluang'])) {
                $data['target_peluang'] = $this->convertDateFormat($data['target_peluang']);
            }

            // Clean currency values
            if (isset($data['biaya_peluang']) && $data['biaya_peluang']) {
                $data['biaya_peluang'] = preg_replace('/[^\d]/', '', $data['biaya_peluang']);
            }
            if (isset($data['pagu_peluang']) && $data['pagu_peluang']) {
                $data['pagu_peluang'] = preg_replace('/[^\d]/', '', $data['pagu_peluang']);
            }

            $datapeluang->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data peluang berhasil diperbarui.',
                'data' => $datapeluang
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data peluang.'
            ], 500);
        }
    }

    public function destroy(Request $request, DataPeluang $datapeluang)
    {
        try {
            DB::beginTransaction();

            $datapeluang->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data peluang berhasil dihapus.'
                ]);
            }

            return redirect()->route('datapeluang.index')
                           ->with('success', 'Data peluang berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data peluang.'
                ], 500);
            }

            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data peluang.']);
        }
    }
}
