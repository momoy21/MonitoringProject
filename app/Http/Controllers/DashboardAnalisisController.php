<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\HistoryProyek;
use App\Models\AktualBiaya;
use App\Models\RABProyek;

class DashboardAnalisisController extends Controller
{
    /**
     * Display the Dashboard Analisis Proyek page.
     */
    public function index()
    {
        return view('dashboardanalisis.index');
    }

    /**
     * Get Deviasi Biaya dashboard data (KPI + chart + table).
     * Query: history_proyek LEFT JOIN aktual_biaya (aggregated)
     * Filter: status IN ('O','I')
     */
    public function getDeviasiBiayaData(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'cost_center');
        $sortDir = $request->input('sort_dir', 'asc');

        // Allowed sort columns
        $allowedSorts = ['cost_center', 'namaproject', 'nilai_proyek', 'total_aktual_biaya', 'deviasi_biaya', 'margin_persen'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'cost_center';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        // Base query per spec
        $query = DB::table('history_proyek as hp')
            ->leftJoin(DB::raw('(SELECT cc_projek, SUM(nilai) AS total_aktual_biaya FROM aktual_biaya GROUP BY cc_projek) as ab'), 'hp.cost_center', '=', 'ab.cc_projek')
            ->select([
                'hp.cost_center',
                'hp.namaproject',
                DB::raw('CAST(hp.nilai_proyek AS DECIMAL(16,2)) as nilai_proyek'),
                DB::raw('COALESCE(ab.total_aktual_biaya, 0) AS total_aktual_biaya'),
                DB::raw('(CAST(hp.nilai_proyek AS DECIMAL(16,2)) - COALESCE(ab.total_aktual_biaya, 0)) AS deviasi_biaya'),
                DB::raw('CASE WHEN CAST(hp.nilai_proyek AS DECIMAL(16,2)) = 0 THEN 0 ELSE ((CAST(hp.nilai_proyek AS DECIMAL(16,2)) - COALESCE(ab.total_aktual_biaya, 0)) / CAST(hp.nilai_proyek AS DECIMAL(16,2))) * 100 END AS margin_persen'),
            ])
            ->whereIn('hp.status', ['O', 'I']);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('hp.cost_center', 'like', "%{$search}%")
                  ->orWhere('hp.namaproject', 'like', "%{$search}%");
            });
        }

        // Get ALL records for KPI calculation (before pagination)
        $allData = (clone $query)->get();

        $totalNilaiProyek = $allData->sum('nilai_proyek');
        $totalAktualBiaya = $allData->sum('total_aktual_biaya');
        $totalDeviasi = $totalNilaiProyek - $totalAktualBiaya;
        $projectOverbudget = $allData->where('deviasi_biaya', '<', 0)->count();

        // Sort and paginate for table
        $total = $allData->count();
        $sorted = $sortDir === 'asc'
            ? $allData->sortBy($sortBy)->values()
            : $allData->sortByDesc($sortBy)->values();
        $paginated = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        // Chart data: use paginated data (current page)
        $chartData = $paginated;

        return response()->json([
            'kpi' => [
                'total_nilai_proyek' => round($totalNilaiProyek, 2),
                'total_aktual_biaya' => round($totalAktualBiaya, 2),
                'total_deviasi' => round($totalDeviasi, 2),
                'project_overbudget' => $projectOverbudget,
            ],
            'chart' => [
                'labels' => $chartData->pluck('cost_center'),
                'nilai_proyek' => $chartData->pluck('nilai_proyek'),
                'total_aktual_biaya' => $chartData->pluck('total_aktual_biaya'),
                'deviasi_biaya' => $chartData->pluck('deviasi_biaya'),
            ],
            'table' => [
                'data' => $paginated->map(function ($item) {
                    return [
                        'cost_center' => $item->cost_center,
                        'namaproject' => $item->namaproject,
                        'nilai_proyek' => round($item->nilai_proyek, 2),
                        'total_aktual_biaya' => round($item->total_aktual_biaya, 2),
                        'deviasi_biaya' => round($item->deviasi_biaya, 2),
                        'margin_persen' => round($item->margin_persen, 1),
                    ];
                }),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get Margin Proyek dashboard data (KPI + chart + table).
     * Query: rab_proyek with margin_rkap & margin_pleno
     */
    public function getMarginProyekData(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'cost_center');
        $sortDir = $request->input('sort_dir', 'asc');

        $allowedSorts = ['cost_center', 'nama_project', 'margin_rkap', 'margin_pleno', 'deviasi_margin', 'persen_margin'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'cost_center';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        // Base query
        $query = DB::table('rab_proyek')
            ->select([
                'cost_center',
                'nama_project',
                DB::raw('COALESCE(margin_rkap, 0) as margin_rkap'),
                DB::raw('COALESCE(margin_pleno, 0) as margin_pleno'),
                DB::raw('(COALESCE(margin_pleno, 0) - COALESCE(margin_rkap, 0)) as deviasi_margin'),
                DB::raw('CASE WHEN COALESCE(margin_rkap, 0) = 0 THEN 0 ELSE ((COALESCE(margin_pleno, 0) - COALESCE(margin_rkap, 0)) / COALESCE(margin_rkap, 0)) * 100 END as persen_margin'),
            ]);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cost_center', 'like', "%{$search}%")
                  ->orWhere('nama_project', 'like', "%{$search}%");
            });
        }

        // Get ALL records for KPI
        $allData = (clone $query)->get();

        $avgMargin = $allData->avg('margin_pleno');
        $maxMargin = $allData->max('margin_pleno');
        $minMargin = $allData->min('margin_pleno');
        $proyekRugi = $allData->where('margin_pleno', '<', 0)->count();

        // Sort and paginate for table
        $total = $allData->count();
        $sorted = $sortDir === 'asc'
            ? $allData->sortBy($sortBy)->values()
            : $allData->sortByDesc($sortBy)->values();
        $paginated = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        // Chart data: use paginated data (current page)
        $chartData = $paginated;


        return response()->json([
            'kpi' => [
                'rata_rata_margin' => round($avgMargin, 1),
                'margin_tertinggi' => round($maxMargin, 1),
                'margin_terendah' => round($minMargin, 1),
                'proyek_rugi' => $proyekRugi,
            ],
            'chart' => [
                'labels' => $chartData->pluck('cost_center'),
                'deviasi_margin' => $chartData->pluck('deviasi_margin'),
                'margin_pleno' => $chartData->pluck('margin_pleno'),
            ],
            'table' => [
                'data' => $paginated->map(function ($item) {
                    return [
                        'cost_center' => $item->cost_center,
                        'nama_project' => $item->nama_project,
                        'margin_rkap' => round($item->margin_rkap, 1),
                        'margin_pleno' => round($item->margin_pleno, 1),
                        'deviasi_margin' => round($item->deviasi_margin, 1),
                        'persen_margin' => round($item->persen_margin, 1),
                    ];
                }),
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get search suggestions (max 3) for autocomplete.
     * Returns "cost_center - nama_project" format.
     */
    public function getSuggestions(Request $request)
    {
        $q = $request->input('q', '');
        $type = $request->input('type', 'deviasi');

        if (strlen($q) < 1) {
            return response()->json([]);
        }

        if ($type === 'deviasi') {
            $results = DB::table('history_proyek')
                ->whereIn('status', ['O', 'I'])
                ->where(function ($query) use ($q) {
                    $query->where('cost_center', 'like', "%{$q}%")
                          ->orWhere('namaproject', 'like', "%{$q}%");
                })
                ->select('cost_center', 'namaproject as nama_project')
                ->distinct()
                ->limit(3)
                ->get();
        } else {
            $results = DB::table('rab_proyek')
                ->where(function ($query) use ($q) {
                    $query->where('cost_center', 'like', "%{$q}%")
                          ->orWhere('nama_project', 'like', "%{$q}%");
                })
                ->select('cost_center', 'nama_project')
                ->distinct()
                ->limit(3)
                ->get();
        }

        return response()->json($results->map(fn($r) => [
            'cost_center' => $r->cost_center,
            'nama_project' => $r->nama_project,
            'label' => $r->cost_center . ' - ' . ($r->nama_project ?? ''),
        ])->values());
    }
}
