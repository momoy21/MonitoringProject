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
                $query->where(function ($q) use ($search) {
                    $q->where('kode_jenis', 'LIKE', "%{$search}%")
                        ->orWhere('nama_jenis', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 10);
            $dataJenis = $query->orderBy('kode_jenis', 'asc')
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
            return redirect()->back()->with('error', 'Terjadi kesalahan.');
        }
    }

    public function create()
    {
        return view('jenisproyek.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:50',
            'status' => 'required|in:A,N'
        ]);

        try {
            DB::beginTransaction();

            // Logika Auto-increment ID (P1, P2, dst)
            $last = JenisProyek::orderBy('kode_jenis', 'desc')->lockForUpdate()->first();
            $num = $last ? intval(substr($last->kode_jenis, 1)) + 1 : 1;

            if ($num > 99) {
                return redirect()->back()->with('error', 'ID Penuh (Maksimal P99).')->withInput();
            }

            JenisProyek::create([
                'kode_jenis' => 'P' . $num,
                'nama_jenis' => $validated['nama_jenis'],
                'status' => $validated['status']
            ]);

            DB::commit();
            return redirect()->route('jenisproyek.index')->with('success', 'Data berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $item = JenisProyek::where('kode_jenis', $id)->firstOrFail();
            return view('jenisproyek.edit', compact('item'));
        } catch (\Exception $e) {
            return redirect()->route('jenisproyek.index')->with('error', 'Data tidak ditemukan.');
        }
    }

    public function update(Request $request, $id)
    {
        $item = JenisProyek::where('kode_jenis', $id)->firstOrFail();

        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:50',
            'status' => 'required|in:A,N'
        ]);

        try {
            DB::beginTransaction();
            $item->update($validated);
            DB::commit();

            return redirect()->route('jenisproyek.index')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function destroy($id)
    {
        try {
            JenisProyek::where('kode_jenis', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus.'], 500);
        }
    }
}
