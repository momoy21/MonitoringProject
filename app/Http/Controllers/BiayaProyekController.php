<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderRAB;
use App\Models\DetailRAB;
use App\Models\AktualBiaya;
use App\Models\SpesifikasiRAB;
use App\Models\PendapatanProyek;
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
            $headerRab = HeaderRAB::where('id_rab', $idRab)->firstOrFail();
            $historyProyek = DB::table('history_proyek')
                ->where('id_project', $headerRab->id_project)
                ->where('norut', $headerRab->norut)
                ->first();

            $currentMonth = Carbon::now()->startOfMonth();

            return response()->json([
                'success' => true,
                'data' => [
                    'pendapatan' => $this->getPendapatanData($idRab, $headerRab->id_project, $headerRab->norut, $currentMonth),
                    'hpp' => $this->getHPPData($idRab, $historyProyek->cost_center ?? null, $currentMonth),
                    'current_month' => $currentMonth->format('M Y')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getPendapatanData($idRab, $idProject, $norut, $currentMonth)
    {
        $specs = SpesifikasiRAB::where('kategori', 'PDP')->where('status', 'A')
            ->orderBy('norutspec')->orderBy('id_spec')->get();

        $allPendapatan = PendapatanProyek::where('id_project', $idProject)->where('norut', $norut)->get();
        $result = [];
        $todayStr = $currentMonth->format('Y-m');

        foreach ($specs as $index => $spec) {
            // Rencana Logic per BPS [cite: 110-116]
            $rencanaThisMonth = 0;
            $valCurrent = DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)
                ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') = ?", [$todayStr])->sum('nilai');

            $valSumPast = DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)
                ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$todayStr])->sum('nilai');

            // Implementasi IF/ELSE spesifikasi BPS
            if (Carbon::now()->format('Y-m') == $todayStr) {
                $rencanaThisMonth = $valCurrent; // IF detailrab.bulan = bulan today() [cite: 110-111]
            } elseif (Carbon::now()->format('Y-m') > $todayStr) {
                $rencanaThisMonth = $valSumPast; // ELSEIF detailrab.bulan < bulan today() [cite: 112-113]
            }

            // Aktual Logic per BPS [cite: 117-125]
            $aktualThisMonth = 0;
            foreach ($allPendapatan as $p) {
                if (!$p->periode_mulai) continue;
                $pMonth = Carbon::parse($p->periode_mulai)->format('Y-m');

                if ($pMonth == $todayStr) {
                    $aktualThisMonth += (float)$p->nilai_pendapatan; // IF Month = bulan today() [cite: 118-119]
                } elseif ($pMonth < $todayStr) {
                    $aktualThisMonth += (float)$p->nilai_pendapatan; // ELSEIF < bulan today() SUM [cite: 120-123]
                }
            }

            $rencanaSD = DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)
                ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$todayStr])->sum('nilai');
            $aktualSD = $allPendapatan->filter(fn($p) => Carbon::parse($p->periode_mulai)->format('Y-m') <= $todayStr)
                ->sum('nilai_pendapatan');

            $result[] = [
                'no' => $index + 1,
                'keterangan' => $spec->spec_rab,
                'bulan_ini' => ['rencana' => (float)$rencanaThisMonth, 'aktual' => (float)$aktualThisMonth],
                'sd_bulan_ini' => ['rencana' => (float)$rencanaSD, 'aktual' => (float)$aktualSD],
                'total' => [
                    'rencana' => (float)DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)->sum('nilai'),
                    'aktual' => (float)$allPendapatan->sum('nilai_pendapatan')
                ]
            ];
        }

        return ['items' => $result, 'totals' => $this->calculateTotals($result)];
    }

    private function getHPPData($idRab, $ccProjek, $currentMonth)
    {
        $specs = SpesifikasiRAB::where('kategori', 'HPP')->where('status', 'A')
            ->orderBy('norutspec')->orderBy('id_spec')->get();

        $result = [];
        $todayStr = $currentMonth->format('Y-m');

        foreach ($specs as $index => $spec) {
            $rencanaThisMonth = 0;
            if (Carbon::now()->format('Y-m') == $todayStr) {
                $rencanaThisMonth = DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)
                    ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') = ?", [$todayStr])->sum('nilai');
            } else {
                $rencanaThisMonth = DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)
                    ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$todayStr])->sum('nilai');
            }

            $aktualThisMonth = 0;
            if ($ccProjek) {
                $aktualThisMonth = AktualBiaya::where('cc_projek', $ccProjek)->where('id_spec', $spec->id_spec)
                    ->where('kategori', 'HPP')->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$todayStr])->sum('nilai');
            }

            $result[] = [
                'no' => $index + 1,
                'keterangan' => $spec->spec_rab,
                'bulan_ini' => ['rencana' => (float)$rencanaThisMonth, 'aktual' => (float)$aktualThisMonth],
                'sd_bulan_ini' => [
                    'rencana' => (float)DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)
                        ->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$todayStr])->sum('nilai'),
                    'aktual' => (float)AktualBiaya::where('cc_projek', $ccProjek)->where('id_spec', $spec->id_spec)
                        ->where('kategori', 'HPP')->whereRaw("DATE_FORMAT(bulan, '%Y-%m') <= ?", [$todayStr])->sum('nilai')
                ],
                'total' => [
                    'rencana' => (float)DetailRAB::where('id_rab', $idRab)->where('id_spec', $spec->id_spec)->sum('nilai'),
                    'aktual' => (float)AktualBiaya::where('cc_projek', $ccProjek)->where('id_spec', $spec->id_spec)
                        ->where('kategori', 'HPP')->sum('nilai')
                ]
            ];
        }

        return ['items' => $result, 'totals' => $this->calculateTotals($result)];
    }

    private function calculateTotals($items)
    {
        return [
            'bulan_ini' => [
                'rencana' => array_sum(array_column(array_column($items, 'bulan_ini'), 'rencana')),
                'aktual' => array_sum(array_column(array_column($items, 'bulan_ini'), 'aktual'))
            ],
            'sd_bulan_ini' => [
                'rencana' => array_sum(array_column(array_column($items, 'sd_bulan_ini'), 'rencana')),
                'aktual' => array_sum(array_column(array_column($items, 'sd_bulan_ini'), 'aktual'))
            ],
            'total' => [
                'rencana' => array_sum(array_column(array_column($items, 'total'), 'rencana')),
                'aktual' => array_sum(array_column(array_column($items, 'total'), 'aktual'))
            ]
        ];
    }
}
