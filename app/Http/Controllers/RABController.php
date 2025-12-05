<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeaderRAB;
use App\Models\DetailRAB;
use App\Models\SummaryDetailRAB;
use App\Models\HistoryProyek;
use App\Models\Konsumen;
use App\Services\ExcelRABProcessor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RABController extends Controller
{
    /**
     * Display the Upload RAB page
     */
    public function index()
    {
        return view('rab.upload');
    }

    /**
     * Alias to show the Upload RAB page (keeps routes pointing to RABController@upload working).
     *
     * @return \Illuminate\View\View
     */
    public function upload()
    {
        return $this->index();
    }

    /**
     * Handle POST from the upload form.
     * Delegates to uploadRABExcel which performs validation and processing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        return $this->uploadRABExcel($request);
    }

    /**
     * Get dropdown data for Cost Center - Nama Proyek
     */
    public function getCostCenterProyek(Request $request)
    {
        $search = $request->get('search', '');

        $query = HistoryProyek::with('konsumen')
            ->select('id_project', 'norut', 'cost_center', 'namaproject', 'id_konsumen',
                    'no_kontrak', 'nilai_proyek', 'start_kontrak', 'finish_kontrak', 'id_bidjasa')
            ->whereNotNull('id_project')
            ->whereNotNull('norut');

        // Filter by bidang jasa for PM role
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user) {
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');

            if (!$isSuperAdmin) {
                $allowedIds = $user->getAllowedBidangJasaIds();
                if (!empty($allowedIds)) {
                    $query->whereIn('id_bidjasa', $allowedIds);
                }
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('cost_center', 'LIKE', '%' . $search . '%')
                  ->orWhere('namaproject', 'LIKE', '%' . $search . '%');
            });
        }

        $data = $query->orderBy('cost_center')->get();

        $results = $data->map(function($item) {
            return [
                'id' => $item->id_project . '_' . $item->norut,
                'text' => $item->cost_center . ' - ' . $item->namaproject,
                'cost_center' => $item->cost_center,
                'namaproject' => $item->namaproject,
                'id_project' => $item->id_project,
                'norut' => $item->norut,
                'id_konsumen' => $item->id_konsumen,
                'konsumen_nama' => $item->konsumen ? $item->konsumen->konsumen : '',
                'no_kontrak' => $item->no_kontrak,
                'nilai_proyek' => $item->nilai_proyek,
                'start_kontrak' => $item->start_kontrak ? $item->start_kontrak->format('d/m/Y') : '',
                'finish_kontrak' => $item->finish_kontrak ? $item->finish_kontrak->format('d/m/Y') : ''
            ];
        });

        return response()->json($results);
    }

    /**
     * Check if Header RAB already exists for selected project
     */
    public function checkHeaderRAB(Request $request)
    {
        $ids = explode('_', $request->project_id);
        $id_project = $ids[0];
        $norut = $ids[1];

        $headerRAB = HeaderRAB::where('id_project', $id_project)
                              ->where('norut', $norut)
                              ->first();

        if ($headerRAB) {
            $historyProyek = HistoryProyek::with('konsumen')
                ->where('id_project', $id_project)
                ->where('norut', $norut)
                ->first();

            return response()->json([
                'exists' => true,
                'headerRAB' => $headerRAB,
                'project' => [
                    'id_rab' => $headerRAB->id_rab,
                    'cost_center' => $historyProyek->cost_center,
                    'namaproject' => $historyProyek->namaproject,
                    'konsumen_nama' => $historyProyek->konsumen ? $historyProyek->konsumen->konsumen : '',
                    'no_kontrak' => $historyProyek->no_kontrak,
                    'nilai_proyek' => $historyProyek->nilai_proyek,
                    'start_kontrak' => $historyProyek->start_kontrak ? $historyProyek->start_kontrak->format('d/m/Y') : '',
                    'finish_kontrak' => $historyProyek->finish_kontrak ? $historyProyek->finish_kontrak->format('d/m/Y') : '',
                    'mulai' => $headerRAB->periode_rab ? Carbon::parse($headerRAB->periode_rab)->format('d/m/Y') : '',
                    'lama' => $headerRAB->lama
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Generate auto ID RAB
     */
    public function generateIdRAB()
    {
        $year = date('Y');
        $month = date('m');

        $lastRAB = HeaderRAB::where('id_rab', 'LIKE', $year . $month . '%')
                            ->orderBy('id_rab', 'desc')
                            ->first();

        if ($lastRAB) {
            $lastSequence = intval(substr($lastRAB->id_rab, 6));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $year . $month . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and return ID RAB via AJAX
     */
    public function generateIdRABAjax()
    {
        try {
            $idRAB = $this->generateIdRAB();
            return response()->json([
                'success' => true,
                'id_rab' => $idRAB
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate ID RAB'
            ], 500);
        }
    }

    /**
     * Store Header RAB data
     */
    public function storeHeaderRAB(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'periode_rab' => 'required|date_format:d/m/Y',
            'lama' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $ids = explode('_', $request->project_id);
            $id_project = $ids[0];
            $norut = $ids[1];

            $existingRAB = HeaderRAB::where('id_project', $id_project)
                                   ->where('norut', $norut)
                                   ->first();

            if ($existingRAB) {
                $existingRAB->update([
                    'periode_rab' => Carbon::createFromFormat('d/m/Y', $request->periode_rab)->format('Y-m-d'),
                    'lama' => $request->lama
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data Header RAB berhasil diperbarui!',
                    'id_rab' => $existingRAB->id_rab
                ]);
            } else {
                $idRAB = $this->generateIdRAB();

                $headerRAB = HeaderRAB::create([
                    'id_rab' => $idRAB,
                    'id_project' => $id_project,
                    'norut' => $norut,
                    'periode_rab' => Carbon::createFromFormat('d/m/Y', $request->periode_rab)->format('Y-m-d'),
                    'lama' => $request->lama
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Data Header RAB berhasil disimpan!',
                    'id_rab' => $idRAB
                ]);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing Header RAB periode and lama
     */
    public function updateHeaderRAB(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'periode_rab' => 'required|date',
            'lama' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $ids = explode('_', $request->project_id);
            $id_project = $ids[0];
            $norut = $ids[1];

            $headerRAB = HeaderRAB::where('id_project', $id_project)
                                  ->where('norut', $norut)
                                  ->first();

            if (!$headerRAB) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header RAB tidak ditemukan!'
                ], 404);
            }

            $headerRAB->update([
                'periode_rab' => Carbon::createFromFormat('d/m/Y', $request->periode_rab)->format('Y-m-d'),
                'lama' => $request->lama
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data Header RAB berhasil diperbarui!',
                'periode_rab' => $request->periode_rab,
                'lama' => $request->lama
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload dan process Excel file untuk Detail RAB dan Summary Detail RAB
     */
    public function uploadRABExcel(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'document_rab' => 'required|file|mimes:xls,xlsx,csv|max:25600'
        ]);

        try {
            DB::beginTransaction();

            $ids = explode('_', $request->project_id);
            $id_project = $ids[0];
            $norut = $ids[1];

            $headerRAB = HeaderRAB::where('id_project', $id_project)
                                  ->where('norut', $norut)
                                  ->first();

            if (!$headerRAB) {
                return response()->json([
                    'success' => false,
                    'message' => 'Header RAB belum dibuat. Silakan buat header RAB terlebih dahulu.'
                ], 400);
            }

            // Delete existing detail RAB dan summary detail RAB
            DetailRAB::where('id_rab', $headerRAB->id_rab)->delete();
            SummaryDetailRAB::where('id_rab', $headerRAB->id_rab)->delete();

            // Store uploaded file temporarily
            $file = $request->file('document_rab');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('temp', $filename, 'public');
            $fullPath = storage_path('app/public/' . $filePath);

            // Process Excel file
            $processor = new ExcelRABProcessor();
            $periodeMulai = Carbon::parse($headerRAB->periode_rab)->format('d/m/Y');

            $result = $processor->processExcelFile(
                $fullPath,
                $headerRAB->id_rab,
                $periodeMulai,
                $headerRAB->lama
            );

            // Clean up temporary file
            Storage::disk('public')->delete($filePath);

            if (!$result['success']) {
                DB::rollback();
                return response()->json($result, 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File Excel berhasil diproses. Data Detail RAB dan Summary Detail RAB berhasil disimpan.',
                'data' => $result['data']
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Detail RAB data untuk ditampilkan dalam tabel
     */
    public function getDetailRAB(Request $request)
    {
        $projectId = $request->get('project_id');

        if (!$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Project ID required'
            ], 400);
        }

        $ids = explode('_', $projectId);
        $id_project = $ids[0];
        $norut = $ids[1];

        $headerRAB = HeaderRAB::where('id_project', $id_project)
                              ->where('norut', $norut)
                              ->first();

        if (!$headerRAB) {
            return response()->json([
                'success' => false,
                'message' => 'Header RAB tidak ditemukan'
            ], 404);
        }

        // Get Detail RAB data
        $detailRABs = DetailRAB::with('spesifikasiRAB')
                              ->where('id_rab', $headerRAB->id_rab)
                              ->ordered()
                              ->get();

        // Group by specification
        $groupedData = [];
        $bulanHeaders = [];

        foreach ($detailRABs as $detail) {
            $specId = $detail->id_spec;
            $bulanIndex = $detail->urutbln;

            if (!isset($groupedData[$specId])) {
                $groupedData[$specId] = [
                    'id_spec' => $specId,
                    'keterangan' => $detail->spesifikasiRAB->spec_rab,
                    'values' => []
                ];
            }

            $groupedData[$specId]['values'][$bulanIndex] = [
                'nilai' => $detail->nilai,
                'formatted_nilai' => $detail->formatted_nilai,
                'bulan' => $detail->bulan
            ];

            if (!in_array($detail->bulan, $bulanHeaders)) {
                $bulanHeaders[$bulanIndex] = $detail->bulan;
            }
        }

        ksort($bulanHeaders);

        return response()->json([
            'success' => true,
            'data' => array_values($groupedData),
            'bulan_headers' => array_values($bulanHeaders),
            'id_rab' => $headerRAB->id_rab,
            'periode_mulai' => Carbon::parse($headerRAB->periode_rab)->format('d/m/Y'),
            'lama_bulan' => $headerRAB->lama
        ]);
    }

    /**
     * Get Summary Detail RAB data untuk ditampilkan dalam tabel
     */
    public function getSummaryDetailRAB(Request $request)
    {
        $projectId = $request->get('project_id');

        if (!$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Project ID required'
            ], 400);
        }

        $ids = explode('_', $projectId);
        $id_project = $ids[0];
        $norut = $ids[1];

        $headerRAB = HeaderRAB::where('id_project', $id_project)
                              ->where('norut', $norut)
                              ->first();

        if (!$headerRAB) {
            return response()->json([
                'success' => false,
                'message' => 'Header RAB tidak ditemukan'
            ], 404);
        }

        // Get Summary Detail RAB data
        $summaryDetails = SummaryDetailRAB::with('summaryRAB')
                              ->where('id_rab', $headerRAB->id_rab)
                              ->ordered()
                              ->get();

        $summaryData = $summaryDetails->map(function($item) {
            return [
                'id_summary_rab' => $item->id_summary_rab,
                'idsummary' => $item->idsummary,
                'keterangan' => $item->summaryRAB ? $item->summaryRAB->ketsummaryrab : '-',
                'nilai' => $item->nilai,
                'formatted_nilai' => $item->formatted_nilai
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $summaryData,
            'id_rab' => $headerRAB->id_rab
        ]);
    }
}
