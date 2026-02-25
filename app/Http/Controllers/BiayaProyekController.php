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
            $headerRab = HeaderRAB::where('id_rab', $idRab)->first();
            if (!$headerRab) return response()->json(['success' => false, 'message' => 'RAB not found'], 404);

            $historyProyek = DB::table('history_proyek')
                ->where('id_project', $headerRab->id_project)
                ->where('norut', $headerRab->norut)
                ->first();

            if (!$historyProyek) return response()->json(['success' => false, 'message' => 'History Project not found'], 404);

            $costCenter = $historyProyek->cost_center;
            $idProject  = $headerRab->id_project;

            $bulanInput = $request->get('bulan', Carbon::now()->format('Y-m-d'));
            $periodeAwal = $headerRab->periode_rab;

            return response()->json([
                'success' => true,
                'data' => [
                    'pendapatan' => $this->getPendapatanData($costCenter, $idProject, $headerRab->norut),
                    'hpp' => $this->getHPPData($costCenter, $bulanInput, $periodeAwal),
                    'periode_awal' => $periodeAwal ? Carbon::parse($periodeAwal)->format('M Y') : '-',
                    'current_month' => Carbon::parse($bulanInput)->format('M Y'),
                    'cost_center' => $costCenter,
                    'bulan_input' => $bulanInput,
                    'periode_awal_raw' => $periodeAwal,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

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

    private function getRencanaSDBulan($costCenter, $idSpec, $periodeAwal, $bulan)
    {
        $startDate = Carbon::parse($periodeAwal)->format('Y-m-01');
        $targetDate = Carbon::parse($bulan)->format('Y-m-01');

        $dateExpr = "CONCAT(RIGHT(dr.bulan, 4), '-', LPAD(FIELD(LEFT(dr.bulan, 3), 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'), 2, '0'), '-01')";

        return (float) DB::table('detail_rab as dr')
            ->join('header_rab as hr', 'hr.id_rab', '=', 'dr.id_rab')
            ->join('data_proyek as dp', 'dp.id_project', '=', 'hr.id_project')
            ->where('dp.cost_center', $costCenter)
            ->where('dr.id_spec', $idSpec)
            ->whereRaw("{$dateExpr} >= ?", [$startDate])
            ->whereRaw("{$dateExpr} <= ?", [$targetDate])
            ->sum('dr.nilai');
    }

    private function getAktualBulan($costCenter, $idSpec, $bulan)
    {
        if (!$costCenter) return 0;

        $bulanYM = Carbon::parse($bulan)->format('Y-m');

        return (float) DB::table('spec_rab_detail as s')
            ->join('plsap as p', 's.cost_element', '=', 'p.cost_element')
            ->where('p.cc_projek', $costCenter)
            ->where('s.id_spec', $idSpec)
            ->whereRaw("DATE_FORMAT(p.posting_date, '%Y-%m') = ?", [$bulanYM])
            ->sum('p.amount_local');
    }

    private function getAktualSDBulan($costCenter, $idSpec, $periodeAwal, $bulan)
    {
        if (!$costCenter) return 0;

        $startYM = Carbon::parse($periodeAwal)->format('Y-m');
        $bulanYM = Carbon::parse($bulan)->format('Y-m');

        return (float) DB::table('spec_rab_detail as s')
            ->join('plsap as p', 's.cost_element', '=', 'p.cost_element')
            ->where('p.cc_projek', $costCenter)
            ->where('s.id_spec', $idSpec)
            ->whereRaw("DATE_FORMAT(p.posting_date, '%Y-%m') >= ?", [$startYM])
            ->whereRaw("DATE_FORMAT(p.posting_date, '%Y-%m') <= ?", [$bulanYM])
            ->sum('p.amount_local');
    }

    private function getRencanaTotal($costCenter, $idSpec)
    {
        return (float) DB::table('detail_rab as dr')
            ->join('header_rab as hr', 'hr.id_rab', '=', 'dr.id_rab')
            ->join('data_proyek as dp', 'dp.id_project', '=', 'hr.id_project')
            ->where('dp.cost_center', $costCenter)
            ->where('dr.id_spec', $idSpec)
            ->sum('dr.nilai');
    }

    private function getAktualTotal($costCenter, $idSpec)
    {
        if (!$costCenter) return 0;

        return (float) DB::table('spec_rab_detail as s')
            ->join('plsap as p', 's.cost_element', '=', 'p.cost_element')
            ->where('p.cc_projek', $costCenter)
            ->where('s.id_spec', $idSpec)
            ->sum('p.amount_local');
    }

    // =========================================================================
    // Data Assembly
    // =========================================================================

    private function getPendapatanData($costCenter, $idProject, $norut)
    {
        $beritaAcaras = DB::table('berita_acara_project as ba')
            ->where('ba.id_project', $idProject)
            ->where('ba.norut', $norut)
            ->select('ba.no_ba', 'ba.desc', 'ba.periode_mulai', 'ba.periode_akhir', 'ba.nilai_ba')
            ->orderBy('ba.created_at', 'desc')
            ->get();

        $list = [];
        $grandTotal = 0;

        foreach ($beritaAcaras as $index => $row) {
            $mulai = $row->periode_mulai ? Carbon::parse($row->periode_mulai)->format('d/m/Y') : '-';
            $akhir = $row->periode_akhir ? Carbon::parse($row->periode_akhir)->format('d/m/Y') : '-';
            $bulanStr = ($mulai === '-' && $akhir === '-') ? '-' : (($mulai === $akhir) ? $mulai : "$mulai - $akhir");

            $nilai = (float) ($row->nilai_ba ?? 0);
            $grandTotal += $nilai;

            $list[] = [
                'no'         => $index + 1,
                'keterangan' => $row->desc ?? '-',
                'bulan'      => $bulanStr,
                'total'      => $nilai,
            ];
        }

        return [
            'items'       => $list,
            'grand_total' => $grandTotal,
        ];
    }

    private function getHPPData($costCenter, $bulanInput, $periodeAwal)
    {
        $specs = SpesifikasiRAB::where('kategori', 'HPP')->where('status', 'A')
            ->orderBy('norutspec')->orderBy('id_spec')->get();

        $result = [];

        foreach ($specs as $index => $spec) {
            $rencanaBulanIni = $this->getRencanaBulan($costCenter, $spec->id_spec, $bulanInput);
            $aktualBulanIni  = $this->getAktualBulan($costCenter, $spec->id_spec, $bulanInput);
            $rencanaSDBulan  = $this->getRencanaSDBulan($costCenter, $spec->id_spec, $periodeAwal, $bulanInput);
            $aktualSDBulan   = $this->getAktualSDBulan($costCenter, $spec->id_spec, $periodeAwal, $bulanInput);

            $result[] = [
                'no' => $index + 1,
                'id_spec' => $spec->id_spec,
                'keterangan' => $spec->spec_rab,
                'bulan_ini' => [
                    'rencana' => $rencanaBulanIni,
                    'aktual'  => $aktualBulanIni,
                ],
                'sd_bulan_ini' => [
                    'rencana' => $rencanaSDBulan,
                    'aktual'  => $aktualSDBulan,
                ],
                'total' => [
                    'rencana' => $this->getRencanaTotal($costCenter, $spec->id_spec),
                    'aktual'  => $this->getAktualTotal($costCenter, $spec->id_spec),
                ],
            ];
        }

        return ['items' => $result, 'totals' => $this->calculateTotals($result)];
    }

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

    // =========================================================================
    // HPP Detail
    // =========================================================================

    public function getHPPDetail(Request $request)
    {
        $costCenter  = $request->get('cost_center');
        $idSpec      = $request->get('id_spec');
        $type        = $request->get('type');
        $scope       = $request->get('scope'); 
        $bulanInput  = $request->get('bulan');
        $periodeAwal = $request->get('periode_awal');

        if (!$costCenter || !$idSpec || !$type || !$scope) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap'], 400);
        }

        try {
            $specName = SpesifikasiRAB::where('id_spec', $idSpec)->value('spec_rab') ?? '-';

            if ($type === 'aktual') {
                $items = $this->getAktualDetail($costCenter, $idSpec, $scope, $bulanInput, $periodeAwal);
            } else {
                $items = $this->getRencanaDetail($costCenter, $idSpec, $scope, $bulanInput, $periodeAwal);
            }

            $total = array_sum(array_column($items, 'nilai'));

            return response()->json([
                'success' => true,
                'data' => [
                    'items'     => $items,
                    'total'     => $total,
                    'type'      => $type,
                    'scope'     => $scope,
                    'spec_name' => $specName,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('getHPPDetail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getAktualDetail($costCenter, $idSpec, $scope, $bulanInput, $periodeAwal)
    {
        $bulanYM = Carbon::parse($bulanInput)->format('Y-m');

        $query = DB::table('spec_rab_detail as s')
            ->join('plsap as p', 's.cost_element', '=', 'p.cost_element')
            ->where('p.cc_projek', $costCenter)
            ->where('s.id_spec', $idSpec);

        if ($scope === 'bulan_ini') {
            $query->whereRaw("DATE_FORMAT(p.posting_date, '%Y-%m') = ?", [$bulanYM]);
        } elseif ($scope === 'sd_bulan_ini') {
            $startYM = Carbon::parse($periodeAwal)->format('Y-m');
            $query->whereRaw("DATE_FORMAT(p.posting_date, '%Y-%m') >= ?", [$startYM])
                ->whereRaw("DATE_FORMAT(p.posting_date, '%Y-%m') <= ?", [$bulanYM]);
        }

        $rows = $query->select(
            'p.cc_projek as cost_center',
            DB::raw("DATE_FORMAT(p.posting_date, '%d-%m-%Y') as periode"),
            'p.cost_element',
            's.description_ce',
            'p.amount_local as nilai'
        )
            ->orderBy('p.posting_date', 'desc')
            ->get();

        return $rows->values()->map(function ($row, $index) {
            return [
                'no'             => $index + 1,
                'cost_center'    => $row->cost_center,
                'periode'        => $row->periode,
                'cost_element'   => $row->cost_element,
                'description_ce' => $row->description_ce,
                'nilai'          => (float) $row->nilai,
            ];
        })->toArray();
    }

    private function getRencanaDetail($costCenter, $idSpec, $scope, $bulanInput, $periodeAwal)
    {
        $query = DB::table('data_proyek as dp')
            ->join('header_rab as hr', 'dp.id_project', '=', 'hr.id_project')
            ->join('detail_rab as dr', 'hr.id_rab', '=', 'dr.id_rab')
            ->join('spec_rab as sr', 'dr.id_spec', '=', 'sr.id_spec')
            ->where('dp.cost_center', $costCenter)
            ->where('dr.id_spec', $idSpec);

        if ($scope === 'bulan_ini') {
            $bulanFormatted = Carbon::parse($bulanInput)->format('M Y');
            $query->where('dr.bulan', $bulanFormatted);
        } elseif ($scope === 'sd_bulan_ini') {
            $startDate = Carbon::parse($periodeAwal)->format('Y-m-01');
            $targetDate = Carbon::parse($bulanInput)->format('Y-m-01');
            $dateExpr = "CONCAT(RIGHT(dr.bulan, 4), '-', LPAD(FIELD(LEFT(dr.bulan, 3), 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'), 2, '0'), '-01')";
            $query->whereRaw("{$dateExpr} >= ?", [$startDate])
                ->whereRaw("{$dateExpr} <= ?", [$targetDate]);
        }

        $rows = $query->select(
            'dp.cost_center',
            'dr.bulan',
            'sr.spec_rab as keterangan',
            'dr.nilai'
        )
            ->orderBy('dr.urutbln', 'desc')
            ->get();

        return $rows->values()->map(function ($row, $index) {
            return [
                'no'          => $index + 1,
                'cost_center' => $row->cost_center,
                'periode'     => $row->bulan,
                'keterangan'  => $row->keterangan,
                'nilai'       => (float) $row->nilai,
            ];
        })->toArray();
    }
}
