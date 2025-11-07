<?php

namespace App\Http\Controllers;

use App\Models\SummaryRAB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SummaryRABController extends Controller
{
    public function index(Request $request)
    {
        $query = SummaryRAB::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ketsummaryrab', 'like', "%{$search}%")
                  ->orWhere('norutsummary', 'like', "%{$search}%");
            });
        }

        // Apply custom ordering (norutsummary, created_at)
        $query->ordered();

        // Pagination
        $perPage = $request->input('per_page', 10);
        $summaryrab = $query->paginate($perPage);

        // AJAX request
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $summaryrab->items(),
                'pagination' => [
                    'current_page' => $summaryrab->currentPage(),
                    'last_page' => $summaryrab->lastPage(),
                    'per_page' => $summaryrab->perPage(),
                    'total' => $summaryrab->total(),
                    'from' => $summaryrab->firstItem(),
                    'to' => $summaryrab->lastItem()
                ]
            ]);
        }

        return view('summaryrab.index', compact('summaryrab'));
    }

    public function create()
    {
        $nextIdSummary = SummaryRAB::generateNextIdSummary();

        // Generate next norutsummary based on total data count
        $totalCount = SummaryRAB::count();
        $nextNorutSummary = str_pad($totalCount + 1, 2, '0', STR_PAD_LEFT);

        return view('summaryrab.create', compact('nextIdSummary', 'nextNorutSummary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ketsummaryrab' => 'required|string|max:100',
            'norutsummary' => 'required|string|max:2',
            'status' => 'nullable|in:A,N',
        ], [
            'ketsummaryrab.required' => 'Keterangan Summary RAB harus diisi.',
            'ketsummaryrab.max' => 'Keterangan Summary RAB maksimal 100 karakter.',
            'norutsummary.required' => 'Nomor urut harus diisi.',
            'norutsummary.max' => 'Nomor urut maksimal 2 karakter.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            // Generate idsummary otomatis
            $validated['idsummary'] = SummaryRAB::generateNextIdSummary();

            $summaryrab = SummaryRAB::create($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Summary RAB berhasil ditambahkan.',
                    'data' => $summaryrab
                ]);
            }

            return redirect()->route('summaryrab.index')
                           ->with('success', 'Summary RAB berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan summary RAB: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal menambahkan summary RAB: ' . $e->getMessage());
        }
    }

    public function show(SummaryRAB $summaryrab)
    {
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $summaryrab
            ]);
        }

        return view('summaryrab.show', compact('summaryrab'));
    }

    public function edit(SummaryRAB $summaryrab)
    {
        return view('summaryrab.edit', compact('summaryrab'));
    }

    public function update(Request $request, SummaryRAB $summaryrab)
    {
        $validated = $request->validate([
            'ketsummaryrab' => 'required|string|max:100',
            'norutsummary' => 'required|string|max:2',
            'status' => 'nullable|in:A,N',
        ], [
            'ketsummaryrab.required' => 'Keterangan Summary RAB harus diisi.',
            'ketsummaryrab.max' => 'Keterangan Summary RAB maksimal 100 karakter.',
            'norutsummary.required' => 'Nomor urut harus diisi.',
            'norutsummary.max' => 'Nomor urut maksimal 2 karakter.',
            'status.in' => 'Status harus berupa Aktif atau Non Aktif.',
        ]);

        try {
            DB::beginTransaction();

            $summaryrab->update($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Summary RAB berhasil diperbarui.',
                    'data' => $summaryrab
                ]);
            }

            return redirect()->route('summaryrab.index')
                           ->with('success', 'Summary RAB berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui summary RAB: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui summary RAB: ' . $e->getMessage());
        }
    }

    public function destroy(SummaryRAB $summaryrab)
    {
        try {
            DB::beginTransaction();

            $summaryrab->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Summary RAB berhasil dihapus.'
                ]);
            }

            return redirect()->route('summaryrab.index')
                           ->with('success', 'Summary RAB berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus summary RAB: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Gagal menghapus summary RAB: ' . $e->getMessage());
        }
    }

    /**
     * Get active summary RAB for dropdown (for future use in other modules)
     */
    public function getActiveSummaryRAB(Request $request)
    {
        try {
            $summaryRAB = SummaryRAB::active()
                                  ->ordered()
                                  ->get(['idsummary', 'ketsummaryrab']);

            return response()->json([
                'success' => true,
                'data' => $summaryRAB
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data summary RAB.'
            ], 500);
        }
    }
}
