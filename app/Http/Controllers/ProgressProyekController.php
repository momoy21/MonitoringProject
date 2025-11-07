<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderProgressProyek;
use App\Models\HeaderRAB;
use App\Models\HistoryProyek;
use App\Models\Konsumen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProgressProyekController extends Controller
{
    /**
     * Display the Progress Project page
     */
    public function index()
    {
        return view('progressproyek.index');
    }

    /**
     * Get dropdown data for Header RAB (Cost Center - Nama Proyek with mulai & lama)
     * FIXED VERSION - Only shows data where periode_rab and lama are filled
     */
    public function getHeaderRAB(Request $request)
    {
        $search = $request->get('search', '');

        try {
            // Debug: Check if there's any data in header_rab with periode_rab and lama
            $debugCount = HeaderRAB::whereNotNull('periode_rab')
                ->whereNotNull('lama')
                ->where('lama', '>', 0)
                ->count();

            Log::info('Header RAB count with periode_rab and lama: ' . $debugCount);

            // Main query with proper table aliasing
            $query = DB::table('header_rab')
                ->whereNotNull('header_rab.periode_rab')
                ->whereNotNull('header_rab.lama')
                ->where('header_rab.lama', '>', 0)
                ->join('history_proyek', function($join) {
                    $join->on('header_rab.id_project', '=', 'history_proyek.id_project')
                         ->on('header_rab.norut', '=', 'history_proyek.norut');
                })
                ->leftJoin('konsumen', 'history_proyek.id_konsumen', '=', 'konsumen.id_konsumen')
                ->select(
                    'header_rab.id_rab',
                    'header_rab.id_project',
                    'header_rab.norut',
                    'header_rab.periode_rab',
                    'header_rab.lama',
                    'history_proyek.cost_center',
                    'history_proyek.namaproject',
                    'history_proyek.id_konsumen',
                    'history_proyek.no_kontrak',
                    'history_proyek.nilai_proyek',
                    'history_proyek.start_kontrak',
                    'history_proyek.finish_kontrak',
                    'history_proyek.id_bidjasa',
                    'konsumen.konsumen as konsumen_nama'
                );

            // Filter by bidang jasa for PM role
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if ($user) {
                $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');

                if (!$isSuperAdmin && method_exists($user, 'getAllowedBidangJasaIds')) {
                    $allowedIds = $user->getAllowedBidangJasaIds();
                    if (!empty($allowedIds)) {
                        $query->whereIn('history_proyek.id_bidjasa', $allowedIds);
                    }
                }
            }

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('history_proyek.cost_center', 'LIKE', '%' . $search . '%')
                      ->orWhere('history_proyek.namaproject', 'LIKE', '%' . $search . '%');
                });
            }

            $data = $query->orderBy('header_rab.id_rab', 'desc')->get();

            Log::info('Query result count: ' . $data->count());

            // Transform results
            $results = $data->map(function($item) {
                // Calculate periode_akhir from periode_rab + lama (end of the last month)
                $periodeAkhir = '';
                if ($item->periode_rab && $item->lama) {
                    try {
                        // Add lama months and get the last day of that month
                        $periodeAkhir = Carbon::parse($item->periode_rab)
                            ->addMonths($item->lama - 1)
                            ->endOfMonth()
                            ->format('d/m/Y');
                    } catch (\Exception $e) {
                        Log::error('Error calculating periode_akhir: ' . $e->getMessage());
                    }
                }

                return [
                    'id' => $item->id_rab,
                    'text' => $item->cost_center . ' - ' . $item->namaproject,
                    'id_rab' => $item->id_rab,
                    'cost_center' => $item->cost_center,
                    'namaproject' => $item->namaproject,
                    'id_project' => $item->id_project,
                    'norut' => $item->norut,
                    'id_konsumen' => $item->id_konsumen,
                    'konsumen_nama' => $item->konsumen_nama ?? '',
                    'no_kontrak' => $item->no_kontrak ?? '',
                    'nilai_proyek' => $item->nilai_proyek ?? 0,
                    'start_kontrak' => $item->start_kontrak ? Carbon::parse($item->start_kontrak)->format('d/m/Y') : '',
                    'finish_kontrak' => $item->finish_kontrak ? Carbon::parse($item->finish_kontrak)->format('d/m/Y') : '',
                    'mulai' => $item->periode_rab ? Carbon::parse($item->periode_rab)->format('d/m/Y') : '',
                    'lama' => $item->lama,
                    'akhir' => $periodeAkhir
                ];
            });

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Error in getHeaderRAB: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if Header Progress already exists for the given Header RAB
     */
    public function checkHeaderProgress(Request $request)
    {
        $idRAB = $request->get('id_rab');

        if (!$idRAB) {
            return response()->json([
                'error' => true,
                'message' => 'ID RAB tidak ditemukan'
            ], 400);
        }

        $headerProgress = HeaderProgressProyek::where('id_rab', $idRAB)->first();

        if ($headerProgress) {
            return response()->json([
                'exists' => true,
                'data' => [
                    'id_progress' => $headerProgress->id_progress,
                    'periode_mulai' => Carbon::parse($headerProgress->periode_mulai)->format('d/m/Y'),
                    'lama' => $headerProgress->lama,
                    'periode_akhir' => Carbon::parse($headerProgress->periode_akhir)->format('d/m/Y')
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Show the form for creating a new header progress proyek
     */
    public function create()
    {
        return view('progressproyek.create');
    }

    /**
     * Store a newly created header progress proyek
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_rab' => 'required|exists:header_rab,id_rab',
            'periode_mulai' => 'required|date',
            'lama' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Get header RAB data
            $headerRAB = HeaderRAB::where('id_rab', $request->id_rab)->first();

            if (!$headerRAB) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header RAB tidak ditemukan'
                ], 404);
            }

            // Generate ID Progress
            $idProgress = $this->generateIdProgress();

            // Calculate periode_akhir (end of the last month)
            $lama = (int) $request->lama; // Ensure it's an integer
            $periodeAkhir = Carbon::parse($request->periode_mulai)
                ->addMonths($lama - 1)
                ->endOfMonth();

            // Create header progress proyek
            $headerProgress = HeaderProgressProyek::create([
                'id_progress' => $idProgress,
                'id_rab' => $request->id_rab,
                'id_project' => $headerRAB->id_project,
                'norut' => $headerRAB->norut,
                'periode_mulai' => $request->periode_mulai,
                'lama' => $lama,
                'periode_akhir' => $periodeAkhir
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Header Progress Proyek berhasil dibuat',
                'redirect' => route('progressproyek.show', $headerProgress->id_progress)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat header progress proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified header progress proyek with tabs
     */
    public function show($id)
    {
        $headerProgress = HeaderProgressProyek::with([
            'headerRAB.historyProyek.konsumen'
        ])->findOrFail($id);

        return view('progressproyek.show', compact('headerProgress'));
    }

    /**
     * Show the form for editing the specified header progress proyek
     */
    public function edit($id)
    {
        $headerProgress = HeaderProgressProyek::with([
            'headerRAB.historyProyek.konsumen'
        ])->findOrFail($id);

        return view('progressproyek.edit', compact('headerProgress'));
    }

    /**
     * Update the specified header progress proyek
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'periode_mulai' => 'required|date',
            'lama' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $headerProgress = HeaderProgressProyek::findOrFail($id);

            // Calculate new periode_akhir (end of the last month)
            $lama = (int) $request->lama;
            $periodeAkhir = Carbon::parse($request->periode_mulai)
                ->addMonths($lama - 1)
                ->endOfMonth();

            $headerProgress->update([
                'periode_mulai' => $request->periode_mulai,
                'lama' => $lama,
                'periode_akhir' => $periodeAkhir
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Header Progress Proyek berhasil diupdate',
                'redirect' => route('progressproyek.show', $headerProgress->id_progress)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate header progress proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified header progress proyek
     */
    public function destroy($id)
    {
        try {
            $headerProgress = HeaderProgressProyek::findOrFail($id);
            $headerProgress->delete();

            return response()->json([
                'success' => true,
                'message' => 'Header Progress Proyek berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus header progress proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate auto ID Progress
     */
    private function generateIdProgress()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = $year . $month;

        $lastProgress = HeaderProgressProyek::where('id_progress', 'LIKE', $prefix . '%')
            ->orderBy('id_progress', 'desc')
            ->first();

        if ($lastProgress) {
            $lastNumber = intval(substr($lastProgress->id_progress, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
