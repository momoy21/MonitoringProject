<?php

namespace App\Http\Controllers;

use App\Models\JenisProyek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenisProyekController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = JenisProyek::query();

            // Fitur Pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('idjenisproyek', 'LIKE', "%{$search}%")
                      ->orWhere('jenisproyek', 'LIKE', "%{$search}%");
                });
            }

            // Ambil per_page dari request (default 10)
            $perPage = $request->get('per_page', 10);

            $dataJenis = $query->orderBy('idjenisproyek', 'asc')
                               ->paginate($perPage)
                               ->withQueryString();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $dataJenis->items(),
                    'pagination' => [
                        'current_page' => $dataJenis->currentPage(),
                        'last_page' => $dataJenis->lastPage(),
                        'total' => $dataJenis->total(),
                    ]
                ]);
            }

            return view('jenisproyek.index', compact('dataJenis'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function create()
    {
        return view('jenisproyek.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenisproyek' => 'required|string|max:100',
            'status' => 'required|in:A,N'
        ]);

        try {
            DB::beginTransaction();

            // Logika Auto-increment ID (P1, P2, dst)
            $last = JenisProyek::orderBy('idjenisproyek', 'desc')->lockForUpdate()->first();
            $num = $last ? intval(substr($last->idjenisproyek, 1)) + 1 : 1;

            if ($num > 99) { // Saya naikkan limitnya ke 99 agar lebih fleksibel dari P9
                return redirect()->back()->with('error', 'ID Penuh (Maksimal P99).')->withInput();
            }

            JenisProyek::create([
                'idjenisproyek' => 'P' . $num,
                'jenisproyek' => $validated['jenisproyek'],
                'status' => $validated['status']
            ]);

            DB::commit();
            return redirect()->route('jenisproyek.index')->with('success', 'Jenis Proyek berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $item = JenisProyek::where('idjenisproyek', $id)->firstOrFail();
            return view('jenisproyek.edit', compact('item'));
        } catch (\Exception $e) {
            return redirect()->route('jenisproyek.index')->with('error', 'Data tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        $item = JenisProyek::where('idjenisproyek', $id)->firstOrFail();
        
        $validated = $request->validate([
            'jenisproyek' => 'required|string|max:100',
            'status' => 'required|in:A,N'
        ]);

        try {
            DB::beginTransaction();
            $item->update($validated);
            DB::commit();

            return redirect()->route('jenisproyek.index')->with('success', 'Jenis Proyek berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function destroy($id)
    {
        try {
            JenisProyek::where('idjenisproyek', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Jenis Proyek berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data.'], 500);
        }
    }
}