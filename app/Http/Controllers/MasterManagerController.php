<?php

namespace App\Http\Controllers;

use App\Models\MasterManager;
use App\Models\MasterDivisi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterManagerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MasterManager::with('divisi');

            // Handle search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nik', 'LIKE', "%{$search}%")
                      ->orWhere('nama', 'LIKE', "%{$search}%");
                });
            }

            $managers = $query->orderBy('created_at', 'desc')
                            ->paginate($request->get('per_page', 10));

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $managers->items(),
                    'pagination' => [
                        'current_page' => $managers->currentPage(),
                        'last_page' => $managers->lastPage(),
                        'per_page' => $managers->perPage(),
                        'total' => $managers->total(),
                        'from' => $managers->firstItem(),
                        'to' => $managers->lastItem(),
                    ]
                ]);
            }

            return view('mastermanager.index', compact('managers'));

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
            $divisiList = MasterDivisi::active()->orderBy('nama_divisi')->get();
            return view('mastermanager.create', compact('divisiList'));
        } catch (\Exception $e) {
            return redirect()->route('mastermanager.index')->with('error', 'Terjadi kesalahan saat membuka form tambah.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => [
                'required',
                'string',
                'size:7',
                'regex:/^[A-Za-z0-9]+$/',
                'unique:master_manager,nik'
            ],
            'nama' => [
                'nullable',
                'string',
                'max:100'
            ],
            'status' => [
                'nullable',
                'in:A,N'
            ],
            'kode_divisi' => [
                'nullable',
                'string',
                'exists:master_divisi,kode_divisi'
            ]
        ], [
            'nik.required' => 'NIK harus diisi.',
            'nik.size' => 'NIK harus terdiri dari 7 karakter.',
            'nik.regex' => 'NIK hanya boleh berisi huruf dan angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'nama.max' => 'Nama manager maksimal 100 karakter.',
            'status.in' => 'Status tidak valid.',
            'kode_divisi.exists' => 'Divisi tidak valid.'
        ]);

        try {
            DB::beginTransaction();

            $manager = MasterManager::create($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manager berhasil ditambahkan.',
                    'data' => $manager
                ]);
            }

            return redirect()->route('mastermanager.index')
                           ->with('success', 'Manager berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data.',
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                           ->withInput();
        }
    }

    public function show(string $nik, Request $request)
    {
        try {
            $manager = MasterManager::with('divisi')->where('nik', $nik)->firstOrFail();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $manager
                ]);
            }

            return view('mastermanager.show', compact('manager'));

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            return redirect()->route('mastermanager.index')
                           ->with('error', 'Data manager tidak ditemukan.');
        }
    }

    public function edit(string $nik)
    {
        try {
            $manager = MasterManager::where('nik', $nik)->firstOrFail();
            $divisiList = MasterDivisi::active()->orderBy('nama_divisi')->get();
            return view('mastermanager.edit', compact('manager', 'divisiList'));

        } catch (\Exception $e) {
            return redirect()->route('mastermanager.index')
                           ->with('error', 'Data manager tidak ditemukan.');
        }
    }

    public function update(Request $request, string $nik)
    {
        try {
            $manager = MasterManager::where('nik', $nik)->firstOrFail();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data manager tidak ditemukan.'
                ], 404);
            }

            return redirect()->route('mastermanager.index')
                           ->with('error', 'Data manager tidak ditemukan.');
        }

        $validated = $request->validate([
            'nama' => [
                'nullable',
                'string',
                'max:100'
            ],
            'status' => [
                'nullable',
                'in:A,N'
            ],
            'kode_divisi' => [
                'nullable',
                'string',
                'exists:master_divisi,kode_divisi'
            ]
        ], [
            'nama.max' => 'Nama manager maksimal 100 karakter.',
            'status.in' => 'Status tidak valid.',
            'kode_divisi.exists' => 'Divisi tidak valid.'
        ]);

        try {
            DB::beginTransaction();

            $manager->update($validated);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Manager berhasil diperbarui.',
                    'data' => $manager->fresh()
                ]);
            }

            return redirect()->route('mastermanager.index')
                           ->with('success', 'Manager berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data.',
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Terjadi kesalahan saat memperbarui data.')
                           ->withInput();
        }
    }

    public function destroy(string $nik, Request $request)
    {
        try {
            $manager = MasterManager::where('nik', $nik)->firstOrFail();

            // TODO: Add check for related projects when project table is implemented
            // if ($manager->projects()->exists()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Manager tidak dapat dihapus karena masih terkait dengan proyek.'
            //     ], 400);
            // }

            DB::beginTransaction();

            $managerNama = $manager->nama ?: $manager->nik;
            $manager->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Manager {$managerNama} berhasil dihapus."
                ]);
            }

            return redirect()->route('mastermanager.index')
                           ->with('success', "Manager {$managerNama} berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data.'
                ], 500);
            }

            return redirect()->route('mastermanager.index')
                           ->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    /**
     * Get active managers for dropdown (for future use in project module)
     */
    public function getActiveManagers(Request $request): JsonResponse
    {
        try {
            $managers = MasterManager::where('status', 'A')
                                   ->orderBy('nama')
                                   ->get(['nik', 'nama']);

            return response()->json([
                'success' => true,
                'data' => $managers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data manager.'
            ], 500);
        }
    }
}
