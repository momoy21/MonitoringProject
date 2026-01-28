<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RABProyek;
use App\Models\Konsumen;
use App\Models\BidangJasa;
use App\Models\MasterDivisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PengajuanRABController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);

            $query = RABProyek::with(['konsumen', 'bidangJasa', 'masterDivisi']);

            // Filter by Cost Center
            if ($request->filled('cost_center')) {
                $query->where('cost_center', 'like', '%' . $request->cost_center . '%');
            }

            // Filter by Nama Proyek
            if ($request->filled('nama_proyek')) {
                $query->where('nama_project', 'like', '%' . $request->nama_proyek . '%');
            }

            // Filter by Konsumen
            if ($request->filled('id_konsumen')) {
                $query->where('id_konsumen', $request->id_konsumen);
            }

            $pengajuanRab = $query->orderBy('tgl_input', 'desc')
                                  ->orderBy('nopengajuan', 'desc')
                                  ->paginate($perPage);

            // Get konsumen list for filter dropdown
            $konsumenList = Konsumen::where('status', 'A')->orderBy('konsumen')->get();

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $pengajuanRab->items(),
                    'pagination' => [
                        'current_page' => $pengajuanRab->currentPage(),
                        'last_page' => $pengajuanRab->lastPage(),
                        'per_page' => $pengajuanRab->perPage(),
                        'total' => $pengajuanRab->total(),
                        'from' => $pengajuanRab->firstItem(),
                        'to' => $pengajuanRab->lastItem(),
                    ]
                ]);
            }

            return view('pengajuanrab.index', compact('pengajuanRab', 'konsumenList'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $nextNoPengajuan = RABProyek::generateNoPengajuan();
            $konsumen = Konsumen::where('status', 'A')->orderBy('konsumen')->get();
            $bidangJasa = BidangJasa::active()->orderBy('desc_bidjasa')->get();
            $divisi = MasterDivisi::active()->orderBy('nama_divisi')->get();
            $today = Carbon::today()->format('Y-m-d');

            return view('pengajuanrab.create', compact(
                'nextNoPengajuan',
                'konsumen',
                'bidangJasa',
                'divisi',
                'today'
            ));
        } catch (\Exception $e) {
            return redirect()->route('pengajuanrab.index')
                           ->with('error', 'Terjadi kesalahan saat membuka form tambah: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Clean numeric format before validation (remove thousand separators)
        if ($request->has('nilai_proyek') && $request->nilai_proyek) {
            $request->merge([
                'nilai_proyek' => str_replace(['.', ','], ['', '.'], $request->nilai_proyek)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'dokumen_io' => 'nullable|string|max:9',
            'cost_center' => 'required|string|max:9',
            'nama_project' => 'required|string',
            'id_konsumen' => 'required|string|exists:konsumen,id_konsumen',
            'id_bidjasa' => 'required|string|exists:bidangjasa,id_bidjasa',
            'pm' => 'nullable|string|max:100',
            'divisi' => 'nullable|string|exists:master_divisi,kode_divisi',
            'nilai_proyek' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|in:P,T,R',
            'rab_upload' => 'nullable|file|mimes:xlsx,xls|max:10240',
            'file_upload' => 'nullable|file|mimes:pdf|max:10240',
            'peta_risk_upload' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'cost_center.required' => 'Cost Center wajib diisi.',
            'cost_center.max' => 'Cost Center maksimal 9 karakter.',
            'nama_project.required' => 'Nama Proyek wajib diisi.',
            'id_konsumen.required' => 'Konsumen wajib dipilih.',
            'id_konsumen.exists' => 'Konsumen tidak valid.',
            'id_bidjasa.required' => 'Bidang Jasa wajib dipilih.',
            'id_bidjasa.exists' => 'Bidang Jasa tidak valid.',
            'divisi.exists' => 'Divisi tidak valid.',
            'nilai_proyek.numeric' => 'Nilai Proyek harus berupa angka.',
            'nilai_proyek.min' => 'Nilai Proyek tidak boleh negatif.',
            'keterangan.in' => 'Keterangan tidak valid.',
            'rab_upload.mimes' => 'File RAB harus berformat Excel (xlsx, xls).',
            'rab_upload.max' => 'File RAB maksimal 10MB.',
            'file_upload.mimes' => 'File Kontrak harus berformat PDF.',
            'file_upload.max' => 'File Kontrak maksimal 10MB.',
            'peta_risk_upload.mimes' => 'File Peta Risiko harus berformat PDF.',
            'peta_risk_upload.max' => 'File Peta Risiko maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            $data = [
                'dokumen_io' => $request->dokumen_io,
                'cost_center' => strtoupper($request->cost_center),
                'nama_project' => $request->nama_project,
                'id_konsumen' => $request->id_konsumen,
                'id_bidjasa' => $request->id_bidjasa,
                'pm' => $request->pm,
                'divisi' => $request->divisi,
                'nilai_proyek' => $request->nilai_proyek ?: null,
                'tgl_input' => Carbon::today(),
                'keterangan' => $request->keterangan,
                'progress' => '01', // Default: Dokumen belum diterima
            ];

            // Handle file uploads
            if ($request->hasFile('rab_upload')) {
                $data['rab_upload'] = $this->uploadFile($request->file('rab_upload'), 'pengajuan_rab/rab');
            }

            if ($request->hasFile('file_upload')) {
                $data['file_upload'] = $this->uploadFile($request->file('file_upload'), 'pengajuan_rab/kontrak');
            }

            if ($request->hasFile('peta_risk_upload')) {
                $data['peta_risk_upload'] = $this->uploadFile($request->file('peta_risk_upload'), 'pengajuan_rab/peta_risiko');
            }

            $rabProyek = RABProyek::create($data);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pengajuan RAB berhasil disimpan.',
                    'data' => $rabProyek
                ]);
            }

            return redirect()->route('pengajuanrab.index')
                           ->with('success', 'Data pengajuan RAB berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RABProyek $pengajuanrab, Request $request)
    {
        try {
            $pengajuanrab->load(['konsumen', 'bidangJasa', 'masterDivisi']);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $pengajuanrab
                ]);
            }

            return view('pengajuanrab.show', compact('pengajuanrab'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            return redirect()->route('pengajuanrab.index')
                           ->with('error', 'Data tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RABProyek $pengajuanrab)
    {
        try {
            $pengajuanrab->load(['konsumen', 'bidangJasa', 'masterDivisi']);
            $konsumen = Konsumen::where('status', 'A')->orderBy('konsumen')->get();
            $bidangJasa = BidangJasa::active()->orderBy('desc_bidjasa')->get();
            $divisi = MasterDivisi::active()->orderBy('nama_divisi')->get();

            return view('pengajuanrab.edit', compact(
                'pengajuanrab',
                'konsumen',
                'bidangJasa',
                'divisi'
            ));
        } catch (\Exception $e) {
            return redirect()->route('pengajuanrab.index')
                           ->with('error', 'Terjadi kesalahan saat membuka form edit: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RABProyek $pengajuanrab)
    {
        // Clean numeric format before validation (remove thousand separators)
        $numericFields = ['nilai_proyek', 'margin_rkap', 'margin_pleno'];
        foreach ($numericFields as $field) {
            if ($request->has($field) && $request->$field) {
                $request->merge([
                    $field => str_replace(['.', ','], ['', '.'], $request->$field)
                ]);
            }
        }

        $validator = Validator::make($request->all(), [
            'dokumen_io' => 'nullable|string|max:9',
            'cost_center' => 'required|string|max:9',
            'nama_project' => 'required|string',
            'id_konsumen' => 'required|string|exists:konsumen,id_konsumen',
            'id_bidjasa' => 'required|string|exists:bidangjasa,id_bidjasa',
            'pm' => 'nullable|string|max:100',
            'divisi' => 'nullable|string|exists:master_divisi,kode_divisi',
            'nilai_proyek' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|in:P,T,R',
            'progress' => 'nullable|in:01,02,03,04',
            'hasil_pleno' => 'nullable|in:TT,TR',
            'catatan' => 'nullable|string',
            'margin_rkap' => 'nullable|numeric',
            'margin_pleno' => 'nullable|numeric',
            'rab_upload' => 'nullable|file|mimes:xlsx,xls|max:10240',
            'file_upload' => 'nullable|file|mimes:pdf|max:10240',
            'peta_risk_upload' => 'nullable|file|mimes:pdf|max:10240',
            'hasil_upload' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'cost_center.required' => 'Cost Center wajib diisi.',
            'cost_center.max' => 'Cost Center maksimal 9 karakter.',
            'nama_project.required' => 'Nama Proyek wajib diisi.',
            'id_konsumen.required' => 'Konsumen wajib dipilih.',
            'id_konsumen.exists' => 'Konsumen tidak valid.',
            'id_bidjasa.required' => 'Bidang Jasa wajib dipilih.',
            'id_bidjasa.exists' => 'Bidang Jasa tidak valid.',
            'divisi.exists' => 'Divisi tidak valid.',
            'nilai_proyek.numeric' => 'Nilai Proyek harus berupa angka.',
            'nilai_proyek.min' => 'Nilai Proyek tidak boleh negatif.',
            'keterangan.in' => 'Keterangan tidak valid.',
            'progress.in' => 'Progress tidak valid.',
            'hasil_pleno.in' => 'Hasil Pleno tidak valid.',
            'rab_upload.mimes' => 'File RAB harus berformat Excel (xlsx, xls).',
            'rab_upload.max' => 'File RAB maksimal 10MB.',
            'file_upload.mimes' => 'File Kontrak harus berformat PDF.',
            'file_upload.max' => 'File Kontrak maksimal 10MB.',
            'peta_risk_upload.mimes' => 'File Peta Risiko harus berformat PDF.',
            'peta_risk_upload.max' => 'File Peta Risiko maksimal 10MB.',
            'hasil_upload.mimes' => 'File Hasil Pleno harus berformat PDF.',
            'hasil_upload.max' => 'File Hasil Pleno maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            $data = [
                'dokumen_io' => $request->dokumen_io,
                'cost_center' => strtoupper($request->cost_center),
                'nama_project' => $request->nama_project,
                'id_konsumen' => $request->id_konsumen,
                'id_bidjasa' => $request->id_bidjasa,
                'pm' => $request->pm,
                'divisi' => $request->divisi,
                'nilai_proyek' => $request->nilai_proyek ?: null,
                'keterangan' => $request->keterangan,
                'progress' => $request->progress,
                'hasil_pleno' => $request->hasil_pleno,
                'catatan' => $request->catatan,
                'margin_rkap' => $request->margin_rkap ?: null,
                'margin_pleno' => $request->margin_pleno ?: null,
            ];

            // Handle file uploads - delete old files if new ones are uploaded
            if ($request->hasFile('rab_upload')) {
                if ($pengajuanrab->rab_upload) {
                    Storage::disk('public')->delete($pengajuanrab->rab_upload);
                }
                $data['rab_upload'] = $this->uploadFile($request->file('rab_upload'), 'pengajuan_rab/rab');
            }

            if ($request->hasFile('file_upload')) {
                if ($pengajuanrab->file_upload) {
                    Storage::disk('public')->delete($pengajuanrab->file_upload);
                }
                $data['file_upload'] = $this->uploadFile($request->file('file_upload'), 'pengajuan_rab/kontrak');
            }

            if ($request->hasFile('peta_risk_upload')) {
                if ($pengajuanrab->peta_risk_upload) {
                    Storage::disk('public')->delete($pengajuanrab->peta_risk_upload);
                }
                $data['peta_risk_upload'] = $this->uploadFile($request->file('peta_risk_upload'), 'pengajuan_rab/peta_risiko');
            }

            if ($request->hasFile('hasil_upload')) {
                if ($pengajuanrab->hasil_upload) {
                    Storage::disk('public')->delete($pengajuanrab->hasil_upload);
                }
                $data['hasil_upload'] = $this->uploadFile($request->file('hasil_upload'), 'pengajuan_rab/hasil_pleno');
            }

            $pengajuanrab->update($data);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pengajuan RAB berhasil diperbarui.',
                    'data' => $pengajuanrab->fresh(['konsumen', 'bidangJasa', 'masterDivisi'])
                ]);
            }

            return redirect()->route('pengajuanrab.index')
                           ->with('success', 'Data pengajuan RAB berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RABProyek $pengajuanrab, Request $request)
    {
        try {
            DB::beginTransaction();

            // Delete associated files
            if ($pengajuanrab->rab_upload) {
                Storage::disk('public')->delete($pengajuanrab->rab_upload);
            }
            if ($pengajuanrab->file_upload) {
                Storage::disk('public')->delete($pengajuanrab->file_upload);
            }
            if ($pengajuanrab->peta_risk_upload) {
                Storage::disk('public')->delete($pengajuanrab->peta_risk_upload);
            }
            if ($pengajuanrab->hasil_upload) {
                Storage::disk('public')->delete($pengajuanrab->hasil_upload);
            }

            $pengajuanrab->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pengajuan RAB berhasil dihapus.'
                ]);
            }

            return redirect()->route('pengajuanrab.index')
                           ->with('success', 'Data pengajuan RAB berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Download file
     */
    public function download(RABProyek $pengajuanrab, string $type)
    {
        $fileColumn = match($type) {
            'rab' => 'rab_upload',
            'kontrak' => 'file_upload',
            'peta_risiko' => 'peta_risk_upload',
            'hasil' => 'hasil_upload',
            default => null
        };

        if (!$fileColumn || !$pengajuanrab->$fileColumn) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $filePath = $pengajuanrab->$fileColumn;

        if (!Storage::disk('public')->exists($filePath)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($filePath);
    }

    /**
     * Generate next no pengajuan (AJAX)
     */
    public function generateNoPengajuan()
    {
        try {
            $noPengajuan = RABProyek::generateNoPengajuan();
            return response()->json([
                'success' => true,
                'nopengajuan' => $noPengajuan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to upload file
     */
    private function uploadFile($file, $directory)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);

        // Sanitize filename
        $nameWithoutExtension = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $nameWithoutExtension);
        $nameWithoutExtension = str_replace(' ', '_', $nameWithoutExtension);

        $finalFileName = $nameWithoutExtension . '_' . time() . '.' . $extension;
        $fullPath = $directory . '/' . $finalFileName;

        // Check for duplicates
        $counter = 1;
        while (Storage::disk('public')->exists($fullPath)) {
            $finalFileName = $nameWithoutExtension . '_' . time() . '(' . $counter . ').' . $extension;
            $fullPath = $directory . '/' . $finalFileName;
            $counter++;
        }

        $file->storeAs($directory, $finalFileName, 'public');

        return $fullPath;
    }
}
