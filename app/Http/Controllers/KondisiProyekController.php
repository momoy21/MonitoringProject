<?php

namespace App\Http\Controllers;

use App\Models\KondisiProyek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Validator;

class KondisiProyekController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');

            $query = KondisiProyek::query();

            // Search Handle
            if ($search) {
                $query->search($search);
            }

            $kondisiproyek = $query->orderBy('id_kondisi_proyek', 'asc')
                               ->paginate($perPage);

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $kondisiproyek->items(),
                    'pagination' => [
                        'current_page' => $kondisiproyek->currentPage(),
                        'last_page' => $kondisiproyek->lastPage(),
                        'per_page' => $kondisiproyek->perPage(),
                        'total' => $kondisiproyek->total(),
                        'from' => $kondisiproyek->firstItem(),
                        'to' => $kondisiproyek->lastItem(),
                    ]
                ]);
            }

            return view('kondisiproyek.index', compact('kondisiproyek'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function create()
    {
        try {
            $nextId = KondisiProyek::generateNextId();
            return view('kondisiproyek.create', compact('nextId'));
        } catch (\Exception $e) {
            return redirect()->route('kondisiproyek.index')->with('error', 'Terjadi kesalahan saat membuka form tambah.');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'desc_kondisi_proyek' => 'required|string|max:255',
            'status' => 'required|in:A,N',
        ], [
            'desc_kondisi_proyek.required' => 'Deskripsi kondisi proyek wajib diisi.',
            'desc_kondisi_proyek.max' => 'Deskripsi kondisi proyek maksimal 255 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
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

            $kondisiProyek = KondisiProyek::create([
                'desc_kondisi_proyek' => $request->desc_kondisi_proyek,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data kondisi proyek berhasil disimpan.',
                    'data' => $kondisiProyek
                ]);
            }

            return redirect()->route('kondisiproyek.index')
                           ->with('success', 'Data kondisi proyek berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data.'
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                           ->withInput();
        }
    }

    public function show(KondisiProyek $kondisiproyek, Request $request)
    {
        try {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $kondisiproyek
                ]);
            }

            return view('kondisiproyek.show', compact('kondisiproyek'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            return redirect()->route('kondisiproyek.index')
                           ->with('error', 'Data tidak ditemukan.');
        }
    }

    public function edit(KondisiProyek $kondisiproyek)
    {
        try {
            return view('kondisiproyek.edit', compact('kondisiproyek'));
        } catch (\Exception $e) {
            return redirect()->route('kondisiproyek.index')
                           ->with('error', 'Terjadi kesalahan saat membuka form edit.');
        }
    }

    public function update(Request $request, KondisiProyek $kondisiproyek)
    {
        $validator = Validator::make($request->all(), [
            'desc_kondisi_proyek' => 'required|string|max:255',
            'status' => 'required|in:A,N',
        ], [
            'desc_kondisi_proyek.required' => 'Deskripsi kondisi proyek wajib diisi.',
            'desc_kondisi_proyek.max' => 'Deskripsi kondisi proyek maksimal 255 karakter.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
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

            $kondisiproyek->update([
                'desc_kondisi_proyek' => $request->desc_kondisi_proyek,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data kondisi proyek berhasil diperbarui.',
                    'data' => $kondisiproyek
                ]);
            }

            return redirect()->route('kondisiproyek.index')
                           ->with('success', 'Data kondisi proyek berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data.'
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat memperbarui data.')
                           ->withInput();
        }
    }

    public function destroy(KondisiProyek $kondisiproyek, Request $request)
    {
        try {
            DB::beginTransaction();

            $kondisiproyek->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data kondisi proyek berhasil dihapus.'
                ]);
            }

            return redirect()->route('kondisiproyek.index')
                           ->with('success', 'Data kondisi proyek berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data.'
                ], 500);
            }

            return redirect()->route('kondisiproyek.index')
                           ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
