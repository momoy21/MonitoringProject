<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    /**
     * Display a listing of karyawan
     */
    public function index(Request $request)
    {
        try {
            $query = Karyawan::query();

            // Fitur Pencarian berdasarkan nama (sesuai spesifikasi)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nik', 'LIKE', "%{$search}%")
                      ->orWhere('nama', 'LIKE', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by aktif
            if ($request->filled('aktif')) {
                $query->where('aktif', $request->aktif);
            }

            // Ambil per_page dari request (default 10)
            $perPage = $request->get('per_page', 10);

            $karyawan = $query->orderBy('created_at', 'desc')
                             ->paginate($perPage)
                             ->withQueryString();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $karyawan->items(),
                    'pagination' => [
                        'current_page' => $karyawan->currentPage(),
                        'last_page' => $karyawan->lastPage(),
                        'total' => $karyawan->total(),
                    ]
                ]);
            }

            return view('karyawan.index', compact('karyawan'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new karyawan
     */
    public function create()
    {
        $statusOptions = Karyawan::STATUS_OPTIONS;
        $aktifOptions = Karyawan::AKTIF_OPTIONS;
        
        return view('karyawan.create', compact('statusOptions', 'aktifOptions'));
    }

    /**
     * Store a newly created karyawan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:9|unique:karyawan,nik',
            'nama' => 'required|string|max:100',
            'status' => 'required|in:T,K,J',
            'aktif' => 'required|in:Y,T'
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.max' => 'NIK maksimal 9 karakter',
            'nik.unique' => 'NIK sudah terdaftar',
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 100 karakter',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
            'aktif.required' => 'Status aktif wajib dipilih',
            'aktif.in' => 'Status aktif tidak valid',
        ]);

        try {
            DB::beginTransaction();
            Karyawan::create($validated);
            DB::commit();

            return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified karyawan
     */
    public function show($nik)
    {
        $karyawan = Karyawan::where('nik', $nik)->firstOrFail();
        return response()->json([
            'success' => true,
            'data' => $karyawan
        ]);
    }

    /**
     * Show the form for editing the specified karyawan
     */
    public function edit($nik)
    {
        $karyawan = Karyawan::where('nik', $nik)->firstOrFail();
        $statusOptions = Karyawan::STATUS_OPTIONS;
        $aktifOptions = Karyawan::AKTIF_OPTIONS;
        
        return view('karyawan.edit', compact('karyawan', 'statusOptions', 'aktifOptions'));
    }

    /**
     * Update the specified karyawan
     */
    public function update(Request $request, $nik)
    {
        $karyawan = Karyawan::where('nik', $nik)->firstOrFail();
        
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'status' => 'required|in:T,K,J',
            'aktif' => 'required|in:Y,T'
        ], [
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 100 karakter',
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
            'aktif.required' => 'Status aktif wajib dipilih',
            'aktif.in' => 'Status aktif tidak valid',
        ]);

        try {
            DB::beginTransaction();
            $karyawan->update($validated);
            DB::commit();

            return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified karyawan (soft delete - set aktif = 'T')
     */
    public function destroy($nik)
    {
        try {
            $karyawan = Karyawan::where('nik', $nik)->firstOrFail();
            
            // Soft delete: set aktif = 'T' instead of deleting
            $karyawan->update(['aktif' => 'T']);
            
            return response()->json([
                'success' => true, 
                'message' => 'Karyawan berhasil dinonaktifkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menonaktifkan karyawan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active karyawan for dropdown/select
     */
    public function getActiveKaryawan()
    {
        $karyawan = Karyawan::active()
                           ->orderBy('nama')
                           ->get(['nik', 'nama', 'status']);
        
        return response()->json([
            'success' => true,
            'data' => $karyawan
        ]);
    }
}
