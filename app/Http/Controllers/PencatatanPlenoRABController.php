<?php

namespace App\Http\Controllers;

use App\Models\RABProyek;
use App\Models\Konsumen;
use App\Models\BidangJasa;
use App\Models\MasterDivisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PencatatanPlenoRABController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar pengajuan RAB untuk pencatatan pleno
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');

            $query = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by progress
            if ($request->filled('progress')) {
                $query->where('progress', $request->get('progress'));
            }

            // Filter by hasil_pleno
            if ($request->filled('hasil_pleno')) {
                $query->where('hasil_pleno', $request->get('hasil_pleno'));
            }

            // Universal Search
            if ($request->filled('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('nopengajuan', 'like', "%{$search}%")
                      ->orWhere('cost_center', 'like', "%{$search}%")
                      ->orWhere('nama_project', 'like', "%{$search}%")
                      ->orWhere('dokumen_io', 'like', "%{$search}%")
                      ->orWhere('pm', 'like', "%{$search}%")
                      ->orWhereHas('konsumen', function($konsumenQuery) use ($search) {
                          $konsumenQuery->where('konsumen', 'like', "%{$search}%");
                      })
                      ->orWhereHas('masterDivisi', function($divisiQuery) use ($search) {
                          $divisiQuery->where('nama_divisi', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting by tgl_input
            $sortOrder = $request->get('sort_order', 'desc');
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';

            $rabProyek = $query->orderBy('tgl_input', $sortOrder)
                               ->orderBy('nopengajuan', $sortOrder)
                               ->paginate($perPage);

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $rabProyek->items(),
                    'pagination' => [
                        'current_page' => $rabProyek->currentPage(),
                        'last_page' => $rabProyek->lastPage(),
                        'per_page' => $rabProyek->perPage(),
                        'total' => $rabProyek->total(),
                        'from' => $rabProyek->firstItem(),
                        'to' => $rabProyek->lastItem(),
                    ]
                ]);
            }

            return view('pencatatanpleno.index', compact('rabProyek'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * Form pencatatan hasil pleno RAB
     */
    public function edit(string $nopengajuan)
    {
        try {
            $rabProyek = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi', 'jenisProyek'])
                                  ->where('nopengajuan', $nopengajuan)
                                  ->firstOrFail();

            // Data for dropdowns
            $konsumenList = Konsumen::orderBy('konsumen')->get();
            $bidangJasaList = BidangJasa::orderBy('desc_bidjasa')->get();
            $divisiList = MasterDivisi::active()->orderBy('nama_divisi')->get();

            // Progress options
            $progressOptions = [
                '01' => 'Dokumen belum diterima',
                '02' => 'Proses tanda tangan BOD',
                '03' => 'Revisi RAB',
                '04' => 'Done',
            ];

            // Keterangan options
            $keteranganOptions = [
                'P' => 'Pleno',
                'T' => 'Tidak Pleno',
                'R' => 'Revisi RAB',
            ];

            // Hasil Pleno options
            $hasilPlenoOptions = [
                'TR' => 'Tercapai RKAP',
                'TT' => 'Tidak Tercapai RKAP',
            ];

            // Status options
            $statusOptions = [
                'D' => 'Draft RAB',
                'F' => 'Final RAB',
            ];

            return view('pencatatanpleno.edit', compact(
                'rabProyek',
                'konsumenList',
                'bidangJasaList',
                'divisiList',
                'progressOptions',
                'keteranganOptions',
                'hasilPlenoOptions',
                'statusOptions'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading pencatatan pleno form: ' . $e->getMessage());
            return redirect()->route('pencatatanpleno.index')
                           ->with('error', 'Data pengajuan RAB tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     * Menyimpan hasil pencatatan pleno RAB
     */
    public function update(Request $request, string $nopengajuan)
    {
        try {
            $rabProyek = RABProyek::where('nopengajuan', $nopengajuan)->firstOrFail();

            // Clean numeric format before validation (remove thousand separators)
            if ($request->has('nilai_proyek') && $request->nilai_proyek) {
                $request->merge([
                    'nilai_proyek' => str_replace(['.', ','], ['', '.'], $request->nilai_proyek)
                ]);
            }

            // Validation
            $validated = $request->validate([
                // Informasi Proyek
                'dokumen_io' => 'nullable|string|max:9',
                'cost_center' => 'required|string|max:9',
                'nama_project' => 'required|string',
                'id_konsumen' => 'required|string|exists:konsumen,id_konsumen',
                'id_bidjasa' => 'required|string|exists:bidangjasa,id_bidjasa',
                'divisi' => 'nullable|string|exists:master_divisi,kode_divisi',
                'pm' => 'nullable|string|max:100',
                'nilai_proyek' => 'nullable|numeric|min:0',
                // Pencatatan Pleno
                'progress' => 'nullable|string|max:2',
                'keterangan' => 'nullable|string|max:1',
                'hasil_pleno' => 'nullable|string|max:2',
                'margin_rkap' => 'nullable|numeric|min:0|max:100',
                'margin_pleno' => 'nullable|numeric|min:0|max:100',
                'catatan' => 'nullable|string|max:500',
                'status' => 'nullable|string|max:1',
                'hasil_upload' => 'nullable|file|mimes:pdf|max:10240',
            ], [
                'cost_center.required' => 'Cost Center wajib diisi.',
                'nama_project.required' => 'Nama Proyek wajib diisi.',
                'id_konsumen.required' => 'Konsumen wajib dipilih.',
                'id_konsumen.exists' => 'Konsumen tidak valid.',
                'id_bidjasa.required' => 'Bidang Jasa wajib dipilih.',
                'id_bidjasa.exists' => 'Bidang Jasa tidak valid.',
                'nilai_proyek.numeric' => 'Nilai Proyek harus berupa angka.',
                'hasil_upload.mimes' => 'File harus berformat PDF (.pdf)',
                'hasil_upload.max' => 'Ukuran file maksimal 10MB',
                'margin_rkap.numeric' => 'Margin RKAP harus berupa angka',
                'margin_pleno.numeric' => 'Margin Pleno harus berupa angka',
                'margin_rkap.max' => 'Margin RKAP maksimal 100%',
                'margin_pleno.max' => 'Margin Pleno maksimal 100%',
            ]);

            // Jika progress = Done (04), file upload wajib (kecuali sudah ada file sebelumnya)
            if ($request->progress === '04' && !$request->hasFile('hasil_upload') && empty($rabProyek->hasil_upload)) {
                return back()->withErrors(['hasil_upload' => 'Jika Progress = Done, dokumen RAB Final wajib diunggah'])
                            ->withInput();
            }

            // Update data - Informasi Proyek
            $rabProyek->dokumen_io = $request->dokumen_io ?: null;
            $rabProyek->cost_center = strtoupper($request->cost_center);
            $rabProyek->nama_project = $request->nama_project;
            $rabProyek->id_konsumen = $request->id_konsumen;
            $rabProyek->id_bidjasa = $request->id_bidjasa;
            $rabProyek->divisi = $request->divisi ?: null;
            $rabProyek->pm = $request->pm ?: null;
            $rabProyek->nilai_proyek = $request->nilai_proyek ?: null;

            // Update data - Pencatatan Pleno (convert empty strings to null)
            $rabProyek->progress = $request->progress ?: null;
            $rabProyek->keterangan = $request->keterangan ?: null;
            $rabProyek->hasil_pleno = $request->hasil_pleno ?: null;
            $rabProyek->margin_rkap = $request->margin_rkap ?: null;
            $rabProyek->margin_pleno = $request->margin_pleno ?: null;
            $rabProyek->catatan = $request->catatan ?: null;
            $rabProyek->status = $request->status ?: null;

            // Handle file upload - Dokumen RAB Final
            if ($request->hasFile('hasil_upload')) {
                // Delete old file if exists
                if ($rabProyek->hasil_upload && Storage::disk('public')->exists($rabProyek->hasil_upload)) {
                    Storage::disk('public')->delete($rabProyek->hasil_upload);
                }

                $file = $request->file('hasil_upload');
                $fileName = 'RAB_Final_' . $nopengajuan . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengajuan_rab/hasil_pleno', $fileName, 'public');
                $rabProyek->hasil_upload = $path;
            }

            $rabProyek->save();

            return redirect()->route('pencatatanpleno.index')
                           ->with('success', 'Data pencatatan pleno RAB berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating pencatatan pleno: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     * Detail pengajuan RAB (read-only)
     */
    public function show(string $nopengajuan)
    {
        try {
            $rabProyek = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi', 'jenisProyek'])
                                  ->where('nopengajuan', $nopengajuan)
                                  ->firstOrFail();

            // Progress options for display
            $progressOptions = [
                '01' => 'Dokumen belum diterima',
                '02' => 'Proses tanda tangan BOD',
                '03' => 'Revisi RAB',
                '04' => 'Done',
            ];

            // Keterangan options
            $keteranganOptions = [
                'P' => 'Pleno',
                'T' => 'Tidak Pleno',
                'R' => 'Revisi RAB',
            ];

            // Hasil Pleno options
            $hasilPlenoOptions = [
                'TR' => 'Tercapai RKAP',
                'TT' => 'Tidak Tercapai RKAP',
            ];

            // Status options
            $statusOptions = [
                'D' => 'Draft RAB',
                'F' => 'Final RAB',
            ];

            return view('pencatatanpleno.show', compact(
                'rabProyek',
                'progressOptions',
                'keteranganOptions',
                'hasilPlenoOptions',
                'statusOptions'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading pencatatan pleno detail: ' . $e->getMessage());
            return redirect()->route('pencatatanpleno.index')
                           ->with('error', 'Data pengajuan RAB tidak ditemukan');
        }
    }
}
