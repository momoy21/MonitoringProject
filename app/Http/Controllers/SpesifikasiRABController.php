<?php

namespace App\Http\Controllers;

use App\Models\SpesifikasiRAB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SpesifikasiRABController extends Controller
{
    public function index(Request $request)
    {
        $query = SpesifikasiRAB::query();

        // Search handle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id_spec', 'LIKE', "%{$search}%")
                  ->orWhere('spec_rab', 'LIKE', "%{$search}%")
                  ->orWhere('norutspec', 'LIKE', "%{$search}%")
                  ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        // Ordering berdasarkan norutspec kemudian id_spec
        $spesifikasirab = $query->ordered()
                               ->paginate($request->get('per_page', 10));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $spesifikasirab->items(),
                'pagination' => [
                    'current_page' => $spesifikasirab->currentPage(),
                    'last_page' => $spesifikasirab->lastPage(),
                    'per_page' => $spesifikasirab->perPage(),
                    'total' => $spesifikasirab->total(),
                    'from' => $spesifikasirab->firstItem(),
                    'to' => $spesifikasirab->lastItem()
                ]
            ]);
        }

        return view('spesifikasirab.index', compact('spesifikasirab'));
    }

    public function create()
    {
        $nextid_spec = SpesifikasiRAB::generateNextIdSpec();

        // Generate next norutspec based on total data count
        $totalCount = SpesifikasiRAB::count();
        $nextNorutSpec = str_pad($totalCount + 1, 2, '0', STR_PAD_LEFT);

        return view('spesifikasirab.create', compact('nextid_spec', 'nextNorutSpec'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'spec_rab' => 'required|string|max:100',
            'norutspec' => 'required|string|max:2',
            'kategori' => 'required|in:PDP,HPP',
            'status' => 'nullable|in:A,N',
        ], [
            'spec_rab.required' => 'Spesifikasi RAB harus diisi.',
            'spec_rab.max' => 'Spesifikasi RAB maksimal 100 karakter.',
            'norutspec.required' => 'Nomor urut harus diisi.',
            'norutspec.max' => 'Nomor urut maksimal 2 karakter.',
            'kategori.required' => 'Kategori harus dipilih.',
            'kategori.in' => 'Kategori harus PDP atau HPP.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            // Generate id_spec otomatis
            $validated['id_spec'] = SpesifikasiRAB::generateNextIdSpec();

            $spesifikasirab = SpesifikasiRAB::create($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Spesifikasi RAB berhasil ditambahkan.',
                    'data' => $spesifikasirab
                ]);
            }

            return redirect()->route('spesifikasirab.index')
                           ->with('success', 'Spesifikasi RAB berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menambahkan data.'
                ], 500);
            }

            return back()->withInput()
                        ->withErrors(['error' => 'Terjadi kesalahan saat menambahkan data.']);
        }
    }

    public function show(SpesifikasiRAB $spesifikasirab)
    {
        return response()->json([
            'success' => true,
            'data' => $spesifikasirab
        ]);
    }

    public function edit(SpesifikasiRAB $spesifikasirab)
    {
        return view('spesifikasirab.edit', compact('spesifikasirab'));
    }

    public function update(Request $request, SpesifikasiRAB $spesifikasirab)
    {
        $validated = $request->validate([
            'spec_rab' => 'required|string|max:100',
            'norutspec' => 'required|string|max:2',
            'kategori' => 'required|in:PDP,HPP',
            'status' => 'nullable|in:A,N',
        ], [
            'spec_rab.required' => 'Spesifikasi RAB harus diisi.',
            'spec_rab.max' => 'Spesifikasi RAB maksimal 100 karakter.',
            'norutspec.required' => 'Nomor urut harus diisi.',
            'norutspec.max' => 'Nomor urut maksimal 2 karakter.',
            'kategori.required' => 'Kategori harus dipilih.',
            'kategori.in' => 'Kategori harus PDP atau HPP.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            $spesifikasirab->update($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Spesifikasi RAB berhasil diperbarui.',
                    'data' => $spesifikasirab
                ]);
            }

            return redirect()->route('spesifikasirab.index')
                           ->with('success', 'Spesifikasi RAB berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data.'
                ], 500);
            }

            return back()->withInput()
                        ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data.']);
        }
    }

    public function destroy(SpesifikasiRAB $spesifikasirab)
    {
        try {
            DB::beginTransaction();

            $spesifikasirab->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Spesifikasi RAB berhasil dihapus.'
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
     * Get active spesifikasi RAB for dropdown (for future use in other modules)
     */
    public function getActiveSpesifikasiRAB(Request $request)
    {
        try {
            $spesifikasiRAB = SpesifikasiRAB::active()
                                          ->ordered()
                                          ->get(['id_spec', 'spec_rab', 'kategori']);

            return response()->json([
                'success' => true,
                'data' => $spesifikasiRAB
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data spesifikasi RAB.'
            ], 500);
        }
    }
}
