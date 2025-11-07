<?php

namespace App\Http\Controllers;

use App\Models\BidangJasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BidangJasaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');

            $query = BidangJasa::query();

            // Search Handle
            if ($search) {
                $query->search($search);
            }

            $bidangjasa = $query->orderBy('id_bidjasa', 'asc')
                               ->paginate($perPage);

            // For AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $bidangjasa->items(),
                    'pagination' => [
                        'current_page' => $bidangjasa->currentPage(),
                        'last_page' => $bidangjasa->lastPage(),
                        'per_page' => $bidangjasa->perPage(),
                        'total' => $bidangjasa->total(),
                        'from' => $bidangjasa->firstItem(),
                        'to' => $bidangjasa->lastItem(),
                    ]
                ]);
            }

            return view('bidangjasa.index', compact('bidangjasa'));
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
            $nextId = BidangJasa::generateNextId();
            return view('bidangjasa.create', compact('nextId'));
        } catch (\Exception $e) {
            return redirect()->route('bidangjasa.index')->with('error', 'Terjadi kesalahan saat membuka form tambah.');
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'desc_bidjasa' => 'required|string|max:50|unique:bidangjasa,desc_bidjasa',
            'status' => 'required|in:A,N',
        ], [
            'desc_bidjasa.required' => 'Deskripsi bidang jasa wajib diisi.',
            'desc_bidjasa.max' => 'Deskripsi bidang jasa maksimal 50 karakter.',
            'desc_bidjasa.unique' => 'Deskripsi bidang jasa sudah ada.',
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

            $bidangJasa = BidangJasa::create([
                'desc_bidjasa' => $request->desc_bidjasa,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data bidang jasa berhasil disimpan.',
                    'data' => $bidangJasa
                ]);
            }

            return redirect()->route('bidangjasa.index')
                           ->with('success', 'Data bidang jasa berhasil disimpan.');

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

    public function show(BidangJasa $bidangjasa, Request $request)
    {
        try {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $bidangjasa
                ]);
            }

            return view('bidangjasa.show', compact('bidangjasa'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            return redirect()->route('bidangjasa.index')
                           ->with('error', 'Data tidak ditemukan.');
        }
    }

    public function edit(BidangJasa $bidangjasa)
    {
        try {
            return view('bidangjasa.edit', compact('bidangjasa'));
        } catch (\Exception $e) {
            return redirect()->route('bidangjasa.index')
                           ->with('error', 'Terjadi kesalahan saat membuka form edit.');
        }
    }

    public function update(Request $request, BidangJasa $bidangjasa)
    {
        $validator = Validator::make($request->all(), [
            'desc_bidjasa' => 'required|string|max:50|unique:bidangjasa,desc_bidjasa,' . $bidangjasa->id_bidjasa . ',id_bidjasa',
            'status' => 'required|in:A,N',
        ], [
            'desc_bidjasa.required' => 'Deskripsi bidang jasa wajib diisi.',
            'desc_bidjasa.max' => 'Deskripsi bidang jasa maksimal 50 karakter.',
            'desc_bidjasa.unique' => 'Deskripsi bidang jasa sudah ada.',
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

            $bidangjasa->update([
                'desc_bidjasa' => $request->desc_bidjasa,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data bidang jasa berhasil diperbarui.',
                    'data' => $bidangjasa
                ]);
            }

            return redirect()->route('bidangjasa.index')
                           ->with('success', 'Data bidang jasa berhasil diperbarui.');

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

    public function destroy(BidangJasa $bidangjasa, Request $request)
    {
        try {
            DB::beginTransaction();

            $bidangjasa->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data bidang jasa berhasil dihapus.'
                ]);
            }

            return redirect()->route('bidangjasa.index')
                           ->with('success', 'Data bidang jasa berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data.'
                ], 500);
            }

            return redirect()->route('bidangjasa.index')
                           ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
