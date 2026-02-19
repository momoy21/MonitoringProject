<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderRAB;
use App\Models\SpesifikasiRAB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BiayaProyekController extends Controller
{
    public function index()
    {
        return view('biayaproyek.index');
    }

    public function getCostCenterDropdown(Request $request)
    {
        $search = $request->get('search', '');

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
            ->join('history_proyek as hp', function ($join) {
                $join->on('hr.id_project', '=', 'hp.id_project')
                    ->on('hr.norut', '=', 'hp.norut');
            })
            ->leftJoin('konsumen as k', 'hp.id_konsumen', '=', 'k.id_konsumen')
            ->whereNotNull('hr.periode_rab')
            ->whereNotNull('hr.lama')
            ->where('hr.lama', '>', 0);

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
            $query->where(function ($q) use ($search) {
                $q->where('hr.id_project', 'LIKE', "%{$search}%")
                    ->orWhere('hp.namaproject', 'LIKE', "%{$search}%")
                    ->orWhere('hp.cost_center', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->orderBy('hr.created_at', 'desc')->limit(50)->get()->map(function ($hr) {
            $mulai = $hr->periode_rab ? Carbon::parse($hr->periode_rab)->format('M Y') : '-';
            $akhir = ($hr->periode_rab && $hr->lama) ?
                Carbon::parse($hr->periode_rab)->addMonths($hr->lama - 1)->endOfMonth()->format('M Y') : '-';

            return [
                'id' => $hr->id_rab,
                'text' => "{$hr->cost_center} - {$hr->namaproject} - Rp " . number_format($hr->nilai_proyek, 0, ',', '.'),
                'id_rab' => $hr->id_rab,
                'id_project' => $hr->id_project,
                'norut' => $hr->norut,
                'cost_center' => $hr->cost_center ?? '-',
                'namaproject' => $hr->namaproject ?? '-',
                'konsumen_nama' => $hr->konsumen ?? '-',
                'no_kontrak' => $hr->no_kontrak ?? '-',
                'nilai_proyek' => $hr->nilai_proyek,
                'start_kontrak' => $hr->start_kontrak ? Carbon::parse($hr->start_kontrak)->format('d/m/Y') : '-',
                'finish_kontrak' => $hr->finish_kontrak ? Carbon::parse($hr->finish_kontrak)->format('d/m/Y') : '-',
                'mulai' => $mulai,
                'lama' => $hr->lama ?? '-',
                'akhir' => $akhir
            ];
        });

        return response()->json($results);
    }

    public function getBiayaProyekData(Request $request)
    {
        $idRab = $request->get('id_rab');
        if (!$idRab) return response()->json(['success' => false, 'message' => 'ID RAB harus diisi'], 400);

        try {
            // 1. Ambil Header RAB
            $headerRab = HeaderRAB::where('id_rab', $idRab)->first();
            if (!$headerRab) return response()->json(['success' => false, 'message' => 'RAB not found'], 404);

            // 2. Ambil Cost Center dari History Proyek yang sesuai dengan Header RAB
            $historyProyek = DB::table('history_proyek')
                ->where('id_project', $headerRab->id_project)
                ->where('norut', $headerRab->norut)
                ->first();

            if (!$historyProyek) return response()->json(['success' => false, 'message' => 'History Project not found'], 404);

            $costCenter = $historyProyek->cost_center;
            $idProject  = $headerRab->id_project;

            $bulanInput = $request->get('bulan', Carbon::now()->format('Y-m-d'));

            return response()->json([
                'success' => true,
                'data' => [
                    'pendapatan' => $this->getPendapatanData($costCenter, $idProject),
                    'hpp' => $this->getHPPData($costCenter, $bulanInput),
                    'current_month' => Carbon::parse($bulanInput)->format('M Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // BPS MP10 Revisi 19 Feb 2026: SQL Helper Functions
    // All Rencana functions use cost_center with JOIN through
    // detail_rab → header_rab → data_proyek (on id_project)
    // =========================================================================

    /**
     * getRencanaBulan - Rencana for a specific month
     * JOIN detail_rab → header_rab → data_proyek WHERE cost_center AND bulan = bulan
     */
    private function getRencanaBulan($costCenter, $idSpec, $bulan)
    {
        return (float) DB::table('detail_rab as dr')
            ->join('header_rab as hr', 'hr.id_rab', '=', 'dr.id_rab')
            ->join('data_proyek as dp', 'dp.id_project', '=', 'hr.id_project')
            ->where('dp.cost_center', $costCenter)
            ->where('dr.id_spec', $idSpec)
            ->where('dr.bulan', Carbon::parse($bulan)->format('M Y'))
            ->sum('dr.nilai');
    }

    /**
     * getRencanaSDBulan - Cumulative rencana up to month
     * JOIN detail_rab → header_rab → data_proyek WHERE cost_center AND bulan <= bulan
     */
    private function getRencanaSDBulan($costCenter, $idSpec, $bulan)
    {
        return (float) DB::table('detail_rab as dr')
            ->join('header_rab as hr', 'hr.id_rab', '=', 'dr.id_rab')
            ->join('data_proyek as dp', 'dp.id_project', '=', 'hr.id_project')
            ->where('dp.cost_center', $costCenter)
            ->where('dr.id_spec', $idSpec)
            ->whereRaw("STR_TO_DATE(dr.bulan, '%b %Y') <= STR_TO_DATE(?, '%b %Y')", [Carbon::parse($bulan)->format('M Y')])
            ->sum('dr.nilai');
    }

    /**
     * getRencanaTotal - Total rencana (all months)
     * JOIN detail_rab → header_rab → data_proyek WHERE cost_center
     */
    private function getRencanaTotal($costCenter, $idSpec)
    {
        return (float) DB::table('detail_rab as dr')
            ->join('header_rab as hr', 'hr.id_rab', '=', 'dr.id_rab')
            ->join('data_proyek as dp', 'dp.id_project', '=', 'hr.id_project')
            ->where('dp.cost_center', $costCenter)
            ->where('dr.id_spec', $idSpec)
            ->sum('dr.nilai');
    }

    /**
     * getAktualBulan - Actual HPP for specific month
     * WHERE cc_projek = cost_center AND kategori = 'HPP' AND bulan = bulan
     */
    private function getAktualBulan($costCenter, $idSpec, $bulan)
    {
        if (!$costCenter) return 0;

        return (float) DB::table('aktual_biaya')
            ->where('cc_projek', $costCenter)
            ->where('id_spec', $idSpec)
            ->where('kategori', 'HPP')
            ->where('bulan', Carbon::parse($bulan)->startOfMonth()->format('Y-m-d'))
            ->sum('nilai');
    }

    /**
     * getAktualSDBulan - Cumulative actual up to month
     * WHERE cc_projek = cost_center AND kategori = 'BIAYA' AND bulan <= bulan
     */
    private function getAktualSDBulan($costCenter, $idSpec, $bulan)
    {
        if (!$costCenter) return 0;

        return (float) DB::table('aktual_biaya')
            ->where('cc_projek', $costCenter)
            ->where('id_spec', $idSpec)
            ->where('kategori', 'BIAYA')
            ->where('bulan', '<=', Carbon::parse($bulan)->endOfMonth()->format('Y-m-d'))
            ->sum('nilai');
    }

    /**
     * getAktualTotal - Total actual (all months)
     * WHERE cc_projek = cost_center AND kategori = 'BIAYA'
     */
    private function getAktualTotal($costCenter, $idSpec)
    {
        if (!$costCenter) return 0;

        return (float) DB::table('aktual_biaya')
            ->where('cc_projek', $costCenter)
            ->where('id_spec', $idSpec)
            ->where('kategori', 'BIAYA')
            ->sum('nilai');
    }

    // =========================================================================
    // Data Assembly
    // =========================================================================

    /**
     * Pendapatan data - 4 column list from pendapatan_proyek records
     * JOIN berita_acara_project for desc (keterangan) and periode (bulan)
     * Keterangan: ba.desc, Bulan: ba.periode_mulai + ba.periode_akhir, Total: nilai_pendapatan
     */
    private function getPendapatanData($costCenter, $idProject)
    {
        $query = DB::table('pendapatan_proyek as pp')
            ->join('history_proyek as hp', 'hp.id_project', '=', 'pp.id_project')
            ->leftJoin('berita_acara_project as ba', function ($join) {
                $join->on('ba.norut', '=', 'pp.norut')
                    ->on('ba.id_project', '=', 'pp.id_project')
                    ->on('ba.no_ba', '=', 'pp.no_ba');
            })
            ->where('hp.cost_center', $costCenter)
            ->where('pp.id_project', $idProject)
            ->distinct()
            ->select(
                'ba.desc as ba_desc',
                'ba.periode_mulai as ba_periode_mulai',
                'ba.periode_akhir as ba_periode_akhir',
                'pp.nilai_pendapatan'
            )
            ->orderBy('ba.periode_mulai', 'asc')
            ->get();

        $list = [];
        $grandTotal = 0;

        foreach ($query as $index => $row) {
            // Format rentang bulan dari berita_acara_project (Contoh: Mar 2026 - Jul 2026)
            $mulai = $row->ba_periode_mulai ? Carbon::parse($row->ba_periode_mulai)->translatedFormat('M Y') : '-';
            $akhir = $row->ba_periode_akhir ? Carbon::parse($row->ba_periode_akhir)->translatedFormat('M Y') : '-';
            $bulanStr = ($mulai === '-' && $akhir === '-') ? '-' : (($mulai === $akhir) ? $mulai : "$mulai - $akhir");

            $nilai = (float) $row->nilai_pendapatan;
            $grandTotal += $nilai;

            $list[] = [
                'no'         => $index + 1,
                'keterangan' => $row->ba_desc ?? '-',
                'bulan'      => $bulanStr,
                'total'      => $nilai,
            ];
        }

        return [
            'items'       => $list,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * HPP data - 8 column Rencana/Aktual format
     * Rencana: from detail_rab via cost_center JOIN to data_proyek
     * Aktual: from aktual_biaya with kategori filters per BPS pseudocode
     */
    private function getHPPData($costCenter, $bulanInput)
    {
        $specs = SpesifikasiRAB::where('kategori', 'HPP')->where('status', 'A')
            ->orderBy('norutspec')->orderBy('id_spec')->get();

        $result = [];

        foreach ($specs as $index => $spec) {
            $result[] = [
                'no' => $index + 1,
                'keterangan' => $spec->spec_rab,
                'bulan_ini' => [
                    'rencana' => $this->getRencanaBulan($costCenter, $spec->id_spec, $bulanInput),
                    'aktual'  => $this->getAktualBulan($costCenter, $spec->id_spec, $bulanInput),
                ],
                'sd_bulan_ini' => [
                    'rencana' => $this->getRencanaSDBulan($costCenter, $spec->id_spec, $bulanInput),
                    'aktual'  => $this->getAktualSDBulan($costCenter, $spec->id_spec, $bulanInput),
                ],
                'total' => [
                    'rencana' => $this->getRencanaTotal($costCenter, $spec->id_spec),
                    'aktual'  => $this->getAktualTotal($costCenter, $spec->id_spec),
                ],
            ];
        }

        return ['items' => $result, 'totals' => $this->calculateTotals($result)];
    }

    /**
     * Sum totals across all items
     */
    private function calculateTotals($items)
    {
        return [
            'bulan_ini' => [
                'rencana' => array_sum(array_column(array_column($items, 'bulan_ini'), 'rencana')),
                'aktual'  => array_sum(array_column(array_column($items, 'bulan_ini'), 'aktual')),
            ],
            'sd_bulan_ini' => [
                'rencana' => array_sum(array_column(array_column($items, 'sd_bulan_ini'), 'rencana')),
                'aktual'  => array_sum(array_column(array_column($items, 'sd_bulan_ini'), 'aktual')),
            ],
            'total' => [
                'rencana' => array_sum(array_column(array_column($items, 'total'), 'rencana')),
                'aktual'  => array_sum(array_column(array_column($items, 'total'), 'aktual')),
            ],
        ];
    }
}
