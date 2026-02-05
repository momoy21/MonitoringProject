<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderRAB;
use App\Models\DetailRAB;
use App\Models\AktualBiaya;
use App\Models\SpesifikasiRAB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BiayaProyekController extends Controller
{
    /**
     * Display main page
     */
    public function index()
    {
        return view('biayaproyek.index');
    }

    /**
     * Get Cost Center dropdown list with Header RAB data
     */
    public function getCostCenterDropdown(Request $request)
    {
        $search = $request->get('search', '');

        // Get Header RAB that has periode_rab and lama defined
        $query = DB::table('header_rab as hr')
            ->select(
                'hr.id_rab',
                'hr.id_project',
                'hr.norut',
                'hr.periode_rab',
                'hr.lama',
                'hp.namaproject',
                'hp.cost_center',
                'hp.no_kontrak',
                'hp.nilai_proyek',
                'hp.start_kontrak',
                'hp.finish_kontrak',
                'hp.id_bidjasa',
                'k.konsumen'
            )
            ->join('history_proyek as hp', function($join) {
                $join->on('hr.id_project', '=', 'hp.id_project')
                     ->on('hr.norut', '=', 'hp.norut');
            })
            ->leftJoin('konsumen as k', 'hp.id_konsumen', '=', 'k.id_konsumen')
            ->whereNotNull('hr.periode_rab')
            ->whereNotNull('hr.lama')
            ->where('hr.lama', '>', 0);

        // Filter by bidang jasa for PM role
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user) {
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
            if (!$isSuperAdmin) {
                $allowedIds = $user->getAllowedBidangJasaIds();
                if (!empty($allowedIds)) {
                    $query->whereIn('hp.id_bidjasa', $allowedIds);
                }
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('hr.id_project', 'LIKE', "%{$search}%")
                  ->orWhere('hp.namaproject', 'LIKE', "%{$search}%")
                  ->orWhere('hp.cost_center', 'LIKE', "%{$search}%");
            });
        }

        $query->orderBy('hr.created_at', 'desc')
              ->limit(50);

        $headerRabList = $query->get();

        $results = $headerRabList->map(function($hr) {
            $costCenter = $hr->cost_center ?? '-';
            $namaProyek = $hr->namaproject ?? '-';
            $nilaiProyek = $hr->nilai_proyek ?? 0;

            // Format nilai untuk dropdown text
            $nilaiProyekFormatted = $nilaiProyek ? 'Rp ' . number_format($nilaiProyek, 0, ',', '.') : 'Rp 0';

            // Dropdown text: Cost Center - Nama Proyek - Nilai Proyek
            $dropdownText = "{$costCenter} - {$namaProyek} - {$nilaiProyekFormatted}";

            // Calculate mulai, lama, akhir from Header RAB
            $mulai = $hr->periode_rab ? Carbon::parse($hr->periode_rab)->format('M Y') : '-';
            $lama = $hr->lama ?? '-';
            $akhir = '-';
            if ($hr->periode_rab && $hr->lama) {
                $akhir = Carbon::parse($hr->periode_rab)
                    ->addMonths($hr->lama - 1)
                    ->endOfMonth()
                    ->format('M Y');
            }

            return [
                'id' => $hr->id_rab,
                'text' => $dropdownText,
                'id_rab' => $hr->id_rab,
                'id_project' => $hr->id_project,
                'norut' => $hr->norut,

                // Data dari History Proyek
                'cost_center' => $costCenter,
                'namaproject' => $namaProyek,
                'konsumen_nama' => $hr->konsumen ?? '-',
                'no_kontrak' => $hr->no_kontrak ?? '-',
                'nilai_proyek' => $nilaiProyek,
                'start_kontrak' => $hr->start_kontrak ?
                    Carbon::parse($hr->start_kontrak)->format('d/m/Y') : '-',
                'finish_kontrak' => $hr->finish_kontrak ?
                    Carbon::parse($hr->finish_kontrak)->format('d/m/Y') : '-',

                // Data dari Header RAB
                'mulai' => $mulai,
                'lama' => $lama,
                'akhir' => $akhir
            ];
        });

        Log::info('Cost Center dropdown data fetched', [
            'count' => $results->count(),
            'search' => $search
        ]);

        return response()->json($results);
    }

    /**
     * Get Biaya Proyek data (Pendapatan and HPP - Rencana vs Aktual)
     */
    public function getBiayaProyekData(Request $request)
    {
        $idRab = $request->get('id_rab');
        
        if (!$idRab) {
            return response()->json([
                'success' => false,
                'message' => 'ID RAB harus diisi'
            ], 400);
        }

        try {
            // Get Header RAB to retrieve id_project and periode_rab
            $headerRab = HeaderRAB::where('id_rab', $idRab)->first();
            
            if (!$headerRab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header RAB tidak ditemukan'
                ], 404);
            }

            // Get history proyek for cost_center
            $historyProyek = DB::table('history_proyek')
                ->where('id_project', $headerRab->id_project)
                ->where('norut', $headerRab->norut)
                ->first();

            $ccProjek = $historyProyek->cost_center ?? null;
            
            // Current month (start of month)
            $currentMonth = Carbon::now()->startOfMonth();
            
            // Get Pendapatan data (kategori = 'PDP')
            $pendapatanData = $this->getCategoryData($idRab, $ccProjek, 'PDP', $currentMonth);
            
            // Get HPP data (kategori = 'HPP')
            $hppData = $this->getCategoryData($idRab, $ccProjek, 'HPP', $currentMonth);

            return response()->json([
                'success' => true,
                'data' => [
                    'pendapatan' => $pendapatanData,
                    'hpp' => $hppData,
                    'current_month' => $currentMonth->format('M Y')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting Biaya Proyek data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get data for a specific category (PDP or HPP)
     */
    private function getCategoryData($idRab, $ccProjek, $kategori, $currentMonth)
    {
        // Get active specs for this category
        $specs = SpesifikasiRAB::where('kategori', $kategori)
            ->where('status', 'A')
            ->orderBy('norutspec', 'asc')
            ->orderBy('id_spec', 'asc')
            ->get();

        $result = [];
        $no = 1;

        foreach ($specs as $spec) {
            // Get Rencana values from detail_rab
            $rencanaThisMonth = DetailRAB::where('id_rab', $idRab)
                ->where('id_spec', $spec->id_spec)
                ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') = ?", [$currentMonth->format('Y-m')])
                ->sum('nilai') ?? 0;

            $rencanaAccumulated = DetailRAB::where('id_rab', $idRab)
                ->where('id_spec', $spec->id_spec)
                ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$currentMonth->format('Y-m')])
                ->sum('nilai') ?? 0;

            $rencanaTotal = DetailRAB::where('id_rab', $idRab)
                ->where('id_spec', $spec->id_spec)
                ->sum('nilai') ?? 0;

            // Get Aktual values from aktual_biaya (if cc_projek is available)
            $aktualThisMonth = 0;
            $aktualAccumulated = 0;
            $aktualTotal = 0;

            if ($ccProjek) {
                $aktualThisMonth = AktualBiaya::where('cc_projek', $ccProjek)
                    ->where('id_spec', $spec->id_spec)
                    ->where('kategori', $kategori)
                    ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') = ?", [$currentMonth->format('Y-m')])
                    ->sum('nilai') ?? 0;

                $aktualAccumulated = AktualBiaya::where('cc_projek', $ccProjek)
                    ->where('id_spec', $spec->id_spec)
                    ->where('kategori', $kategori)
                    ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$currentMonth->format('Y-m')])
                    ->sum('nilai') ?? 0;

                $aktualTotal = AktualBiaya::where('cc_projek', $ccProjek)
                    ->where('id_spec', $spec->id_spec)
                    ->where('kategori', $kategori)
                    ->sum('nilai') ?? 0;
            }

            $result[] = [
                'no' => $no++,
                'keterangan' => $spec->spec_rab,
                'id_spec' => $spec->id_spec,
                'bulan_ini' => [
                    'rencana' => (float) $rencanaThisMonth,
                    'aktual' => (float) $aktualThisMonth
                ],
                'sd_bulan_ini' => [
                    'rencana' => (float) $rencanaAccumulated,
                    'aktual' => (float) $aktualAccumulated
                ],
                'total' => [
                    'rencana' => (float) $rencanaTotal,
                    'aktual' => (float) $aktualTotal
                ]
            ];
        }

        // Calculate totals
        $totals = [
            'bulan_ini' => [
                'rencana' => array_sum(array_column(array_column($result, 'bulan_ini'), 'rencana')),
                'aktual' => array_sum(array_column(array_column($result, 'bulan_ini'), 'aktual'))
            ],
            'sd_bulan_ini' => [
                'rencana' => array_sum(array_column(array_column($result, 'sd_bulan_ini'), 'rencana')),
                'aktual' => array_sum(array_column(array_column($result, 'sd_bulan_ini'), 'aktual'))
            ],
            'total' => [
                'rencana' => array_sum(array_column(array_column($result, 'total'), 'rencana')),
                'aktual' => array_sum(array_column(array_column($result, 'total'), 'aktual'))
            ]
        ];

        return [
            'items' => $result,
            'totals' => $totals
        ];
    }
}
