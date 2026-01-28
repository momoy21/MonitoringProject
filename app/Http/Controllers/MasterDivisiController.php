<?php

namespace App\Http\Controllers;

use App\Models\MasterDivisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDivisiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MasterDivisi::query();

            // Fitur Pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('kode_divisi', 'LIKE', "%{$search}%")
                      ->orWhere('nama_divisi', 'LIKE', "%{$search}%");
                });
            }

            // Ambil per_page dari request (default 10)
            $perPage = $request->get('per_page', 10);

            $divisi = $query->orderBy('created_at', 'desc')
                           ->paginate($perPage) // Gunakan variabel dinamis
                           ->withQueryString(); // Menjaga parameter URL tetap ada

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $divisi->items(),
                    'pagination' => [
                        'current_page' => $divisi->currentPage(),
                        'last_page' => $divisi->lastPage(),
                        'total' => $divisi->total(),
                    ]
                ]);
            }

            return view('masterdivisi.index', compact('divisi'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function create()
    {
        return view('masterdivisi.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_divisi' => 'required|string|max:10|unique:master_divisi,kode_divisi',
            'nama_divisi' => 'nullable|string|max:100',
            'status' => 'nullable|in:A,N'
        ]);

        try {
            DB::beginTransaction(); // Menjamin keamanan data
            MasterDivisi::create($validated);
            DB::commit();

            return redirect()->route('masterdivisi.index')->with('success', 'Divisi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data.')->withInput();
        }
    }

    public function edit($kode_divisi)
    {
        $divisi = MasterDivisi::where('kode_divisi', $kode_divisi)->firstOrFail();
        return view('masterdivisi.edit', compact('divisi'));
    }

    public function update(Request $request, $kode_divisi)
    {
        $divisi = MasterDivisi::where('kode_divisi', $kode_divisi)->firstOrFail();
        
        $validated = $request->validate([
            'nama_divisi' => 'nullable|string|max:100',
            'status' => 'nullable|in:A,N'
        ]);

        try {
            DB::beginTransaction();
            $divisi->update($validated);
            DB::commit();

            return redirect()->route('masterdivisi.index')->with('success', 'Divisi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function destroy($kode_divisi)
    {
        try {
            MasterDivisi::where('kode_divisi', $kode_divisi)->delete();
            return response()->json(['success' => true, 'message' => 'Divisi dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal hapus.'], 500);
        }
    }
}