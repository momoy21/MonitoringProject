<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\HeaderPenugasan;
use App\Models\HistoryProyek;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PenugasanController extends Controller
{
    /* =========================
        UTILITAS
    ========================== */

    private function generateIDPenugasan()
    {
        $today = date('Ymd');
        $lastHeader = HeaderPenugasan::where('IDPenugasan', 'LIKE', $today . '%')
            ->max('IDPenugasan');
        $lastDetail = Penugasan::where('IDPenugasan', 'LIKE', $today . '%')
            ->max('IDPenugasan');

        $last = max($lastHeader, $lastDetail);
        $urut = $last ? intval(substr($last, -2)) + 1 : 1;
        return $today . str_pad($urut, 2, '0', STR_PAD_LEFT);
    }

    private function generateNoSurat()
    {
        $romawi = ["", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"];
        return "…../SPK/DIR-KIT/" . $romawi[date('n')] . "/" . date('Y');
    }

    /* =========================
        INDEX (View Only)
    ========================== */

    public function index()
    {
        return view('penugasan.index');
    }

    /* =========================
        INITIAL DATA (Prefetch)
    ========================== */

    public function getInitialData()
    {
        $costCenters = Cache::remember('penugasan_cost_centers', 300, function () {
            return HistoryProyek::select('cost_center', 'id_project', 'norut', 'namaproject', 'dokumen_io')
                ->whereNotNull('cost_center')
                ->where('cost_center', '!=', '')
                ->whereNotIn('status', ['C', 'F'])
                ->orderBy('cost_center')
                ->get()
                ->map(function ($item) {
                    $costCenter  = $item->cost_center ?? '-';
                    $idProject   = $item->id_project ?? '';
                    $noUrut      = $item->norut ?? 0;
                    $namaProject = $item->namaproject ?? '-';
                    $dokumenIO   = $item->dokumen_io ?? '-';
                    return [
                        'id'           => "{$costCenter}|{$idProject}|{$noUrut}",
                        'text'         => "{$costCenter} - {$namaProject}",
                        'cost_center'  => $costCenter,
                        'id_project'   => $idProject,
                        'no_urut'      => $noUrut,
                        'namaproject'  => $namaProject,
                        'dokumen_io'   => $dokumenIO,
                    ];
                });
        });

        $karyawan = Cache::remember('penugasan_karyawan', 300, function () {
            return Karyawan::active()
                ->orderBy('nama')
                ->get(['nik', 'nama'])
                ->map(function ($item) {
                    return [
                        'id'   => $item->nik,
                        'text' => "{$item->nik} - {$item->nama}",
                        'nik'  => $item->nik,
                        'nama' => $item->nama,
                    ];
                });
        });

        // Headers from header_penugasan
        $headers = HeaderPenugasan::leftJoin('history_proyek', function ($join) {
                $join->on('header_penugasan.id_project', '=', 'history_proyek.id_project')
                     ->on('header_penugasan.no_urut', '=', 'history_proyek.norut');
            })
            ->select(
                'header_penugasan.IDPenugasan',
                'header_penugasan.cost_center',
                'header_penugasan.id_project',
                'header_penugasan.no_urut',
                'header_penugasan.NoSurat',
                'header_penugasan.PejabatTandatangan',
                'history_proyek.namaproject',
                'history_proyek.dokumen_io'
            )
            ->orderBy('header_penugasan.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->IDPenugasan,
                    'text'          => "{$item->IDPenugasan} - {$item->cost_center} - " . ($item->namaproject ?? '-'),
                    'IDPenugasan'   => $item->IDPenugasan,
                    'cost_center'   => $item->cost_center,
                    'id_project'    => $item->id_project ?? '',
                    'no_urut'       => $item->no_urut ?? 0,
                    'NoSurat'       => $item->NoSurat ?? '-',
                    'namaproject'   => $item->namaproject ?? '-',
                    'dokumen_io'    => $item->dokumen_io ?? '-',
                ];
            });

        return response()->json([
            'cost_centers' => $costCenters,
            'karyawan'     => $karyawan,
            'headers'      => $headers,
        ]);
    }

    /* =========================
        GET HEADERS (AJAX)
    ========================== */

    public function getHeaders()
    {
        $headers = HeaderPenugasan::leftJoin('history_proyek', function ($join) {
                $join->on('header_penugasan.id_project', '=', 'history_proyek.id_project')
                     ->on('header_penugasan.no_urut', '=', 'history_proyek.norut');
            })
            ->select(
                'header_penugasan.IDPenugasan',
                'header_penugasan.cost_center',
                'header_penugasan.id_project',
                'header_penugasan.no_urut',
                'header_penugasan.NoSurat',
                'header_penugasan.PejabatTandatangan',
                'history_proyek.namaproject',
                'history_proyek.dokumen_io'
            )
            ->orderBy('header_penugasan.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id'            => $item->IDPenugasan,
                    'text'          => "{$item->IDPenugasan} - {$item->cost_center} - " . ($item->namaproject ?? '-'),
                    'IDPenugasan'   => $item->IDPenugasan,
                    'cost_center'   => $item->cost_center,
                    'id_project'    => $item->id_project ?? '',
                    'no_urut'       => $item->no_urut ?? 0,
                    'NoSurat'       => $item->NoSurat ?? '-',
                    'namaproject'   => $item->namaproject ?? '-',
                    'dokumen_io'    => $item->dokumen_io ?? '-',
                ];
            });

        return response()->json($headers);
    }

    /* =========================
        STORE HEADER (AJAX)
    ========================== */

    public function storeHeader(Request $request)
    {
        $validated = $request->validate([
            'IDPenugasan'  => 'required|string|max:10',
            'cost_center'  => 'required|string|max:9',
            'id_project'   => 'required|string|max:10',
            'no_urut'      => 'required|integer',
            'NoSurat'      => 'required|string|max:50',
        ]);

        // Check if header already exists for this IDPenugasan + cost_center
        $exists = HeaderPenugasan::where('IDPenugasan', $validated['IDPenugasan'])
            ->where('cost_center', $validated['cost_center'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Header dengan ID Penugasan dan Cost Center ini sudah ada'
            ], 422);
        }

        $project = HistoryProyek::where('id_project', $validated['id_project'])
            ->where('norut', $validated['no_urut'])
            ->first();

        try {
            $header = HeaderPenugasan::create([
                'IDPenugasan'        => $validated['IDPenugasan'],
                'cost_center'        => $validated['cost_center'],
                'id_project'         => $validated['id_project'],
                'no_urut'            => $validated['no_urut'],
                'NoSurat'            => $validated['NoSurat'],
                'PejabatTandatangan' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Header penugasan berhasil disimpan',
                'data'    => [
                    'id'            => $header->IDPenugasan,
                    'text'          => "{$header->IDPenugasan} - {$header->cost_center} - " . ($project->namaproject ?? '-'),
                    'IDPenugasan'   => $header->IDPenugasan,
                    'cost_center'   => $header->cost_center,
                    'id_project'    => $header->id_project,
                    'no_urut'       => $header->no_urut,
                    'NoSurat'       => $header->NoSurat,
                    'namaproject'   => $project->namaproject ?? '-',
                    'dokumen_io'    => $project->dokumen_io ?? '-',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing header penugasan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan header: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
        COST CENTER DROPDOWN
    ========================== */

    public function getCostCenterDropdown(Request $request)
    {
        $search = $request->get('search', '');

        $query = HistoryProyek::select('cost_center', 'id_project', 'norut', 'namaproject', 'dokumen_io')
            ->whereNotNull('cost_center')
            ->where('cost_center', '!=', '')
            ->whereNotIn('status', ['C', 'F']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cost_center', 'LIKE', "%{$search}%")
                    ->orWhere('namaproject', 'LIKE', "%{$search}%")
                    ->orWhere('dokumen_io', 'LIKE', "%{$search}%");
            });
        }

        $projects = $query->orderBy('cost_center')->limit(50)->get();

        $results = $projects->map(function ($item) {
            $costCenter  = $item->cost_center ?? '-';
            $idProject   = $item->id_project ?? '';
            $noUrut      = $item->norut ?? 0;
            $namaProject = $item->namaproject ?? '-';
            $dokumenIO   = $item->dokumen_io ?? '-';
            return [
                'id'           => "{$costCenter}|{$idProject}|{$noUrut}",
                'text'         => "{$costCenter} - {$namaProject}",
                'cost_center'  => $costCenter,
                'id_project'   => $idProject,
                'no_urut'      => $noUrut,
                'namaproject'  => $namaProject,
                'dokumen_io'   => $dokumenIO,
            ];
        });

        return response()->json($results);
    }

    /* =========================
        KARYAWAN DROPDOWN
    ========================== */

    public function getKaryawanDropdown(Request $request)
    {
        $search = $request->get('search', '');

        $query = Karyawan::active();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'LIKE', "%{$search}%")
                    ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $karyawan = $query->orderBy('nama')->limit(50)->get(['nik', 'nama']);

        $results = $karyawan->map(function ($item) {
            return [
                'id'   => $item->nik,
                'text' => "{$item->nik} - {$item->nama}",
                'nik'  => $item->nik,
                'nama' => $item->nama,
            ];
        });

        return response()->json($results);
    }

    /* =========================
        GENERATE ID (AJAX)
    ========================== */

    public function generateId()
    {
        return response()->json([
            'IDPenugasan' => $this->generateIDPenugasan(),
            'NoSurat'     => $this->generateNoSurat(),
        ]);
    }

    /* =========================
        GET DATA (Paginated) — by IDPenugasan
    ========================== */

    public function getData(Request $request)
    {
        $idPenugasan = $request->get('id_penugasan');
        $search      = $request->get('search', '');
        $perPage     = $request->get('per_page', 10);

        if (!$idPenugasan) {
            return response()->json([
                'success' => false,
                'message' => 'ID Penugasan harus dipilih'
            ], 400);
        }

        try {
            $query = Penugasan::where('penugasan.IDPenugasan', $idPenugasan)
                ->leftJoin('karyawan', 'penugasan.NIK', '=', 'karyawan.nik')
                ->select(
                    'penugasan.id',
                    'penugasan.IDPenugasan',
                    'penugasan.cost_center',
                    'penugasan.Norut',
                    'penugasan.NIK',
                    'penugasan.NoSurat',
                    'penugasan.Dokumen_IO',
                    'penugasan.Jabatan',
                    'penugasan.Periodeawal',
                    'penugasan.Periodeakhir',
                    'penugasan.Bobot',
                    'penugasan.Status',
                    'penugasan.Keterangan',
                    'karyawan.nama as nama_karyawan'
                );

            if ($search) {
                $words = explode(' ', $search);
                foreach ($words as $word) {
                    $word = trim($word);
                    if ($word === '') continue;
                    $upper = strtoupper($word);
                    $query->where(function ($q) use ($upper) {
                        $q->whereRaw("UPPER(penugasan.NIK) LIKE ?", ['%' . $upper . '%'])
                            ->orWhereRaw("UPPER(karyawan.nama) LIKE ?", ['%' . $upper . '%']);
                    });
                }
            }

            $data = $query->orderBy('penugasan.created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success'    => true,
                'data'       => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                    'per_page'     => $data->perPage(),
                    'total'        => $data->total(),
                    'from'         => $data->firstItem() ?? 0,
                    'to'           => $data->lastItem() ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting penugasan data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
        STORE (AJAX)
    ========================== */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'IDPenugasan'  => 'required|string|max:10',
            'NoSurat'      => 'required|string|max:50',
            'cost_center'  => 'required|string|max:9',
            'NIK'          => 'required|string|max:9',
            'Jabatan'      => 'required|string|max:30',
            'Periodeawal'  => 'required|date',
            'Periodeakhir' => 'required|date',
            'Bobot'        => 'required|numeric|min:0.01|max:100',
            'Status'       => 'required|in:A,N',
            'replace'      => 'nullable|boolean',
        ]);

        $replace = $request->boolean('replace', false);

        if (Carbon::parse($validated['Periodeawal'])->gt(Carbon::parse($validated['Periodeakhir']))) {
            return response()->json([
                'success' => false,
                'message' => 'Periode Awal tidak boleh lebih besar dari Periode Akhir'
            ], 422);
        }

        // Validate header exists
        $headerExists = HeaderPenugasan::where('IDPenugasan', $validated['IDPenugasan'])->exists();
        if (!$headerExists) {
            return response()->json([
                'success' => false,
                'message' => 'Header Penugasan belum disimpan. Klik Simpan Header terlebih dahulu.'
            ], 422);
        }

        $karyawan = Karyawan::where('nik', $validated['NIK'])->first();
        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => "NIK '{$validated['NIK']}' tidak ditemukan di data karyawan"
            ], 422);
        }

        $project = HistoryProyek::where('cost_center', $validated['cost_center'])->first();

        try {
            DB::beginTransaction();

            $newStart = Carbon::parse($validated['Periodeawal']);
            $newEnd   = Carbon::parse($validated['Periodeakhir']);

            // 1. Exact duplicate: CC + NIK + Jabatan (ci) + Periodeawal + Periodeakhir
            $existing = Penugasan::where('cost_center', $validated['cost_center'])
                ->where('NIK', $validated['NIK'])
                ->whereDate('Periodeawal', $validated['Periodeawal'])
                ->whereDate('Periodeakhir', $validated['Periodeakhir'])
                ->whereRaw('LOWER(Jabatan) = ?', [strtolower($validated['Jabatan'])])
                ->first();

            if ($existing) {
                if (!$replace) {
                    DB::rollBack();
                    $nama = $karyawan->nama ?? '-';
                    return response()->json([
                        'success'   => false,
                        'duplicate' => true,
                        'message'   => 'Data duplikat ditemukan',
                        'existing'  => [
                            'id'            => $existing->id,
                            'nik'           => $existing->NIK,
                            'nama'          => $nama,
                            'cost_center'   => $existing->cost_center,
                            'jabatan'       => $existing->Jabatan,
                            'periode_awal'  => $existing->Periodeawal ? $existing->Periodeawal->format('Y-m-d') : null,
                            'periode_akhir' => $existing->Periodeakhir ? $existing->Periodeakhir->format('Y-m-d') : null,
                            'bobot'         => $existing->Bobot,
                            'status'        => $existing->Status,
                        ],
                    ], 409);
                }

                // Replace — only Bobot and Status
                $existing->update([
                    'Bobot'  => $validated['Bobot'],
                    'Status' => $validated['Status'],
                ]);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Data penugasan berhasil diganti (replace)',
                    'data'    => $existing,
                ]);
            }

            // 2. Overlap check: same CC + NIK + Jabatan (ci) → periods must not overlap
            $overlap = Penugasan::where('cost_center', $validated['cost_center'])
                ->where('NIK', $validated['NIK'])
                ->whereRaw('LOWER(Jabatan) = ?', [strtolower($validated['Jabatan'])])
                ->whereDate('Periodeawal', '<=', $newEnd)
                ->whereDate('Periodeakhir', '>=', $newStart)
                ->first();

            if ($overlap) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'overlap' => true,
                    'message' => 'Periode bersinggungan dengan data yang sudah ada',
                    'existing' => [
                        'jabatan'       => $overlap->Jabatan,
                        'periode_awal'  => $overlap->Periodeawal ? $overlap->Periodeawal->format('Y-m-d') : null,
                        'periode_akhir' => $overlap->Periodeakhir ? $overlap->Periodeakhir->format('Y-m-d') : null,
                        'bobot'         => $overlap->Bobot,
                        'status'        => $overlap->Status,
                    ],
                ], 422);
            }

            $norut = Penugasan::where('IDPenugasan', $validated['IDPenugasan'])->max('Norut');
            $norut = ($norut ?? 0) + 1;

            $penugasan = Penugasan::create([
                'IDPenugasan'  => $validated['IDPenugasan'],
                'cost_center'  => $validated['cost_center'],
                'Norut'        => $norut,
                'NIK'          => $validated['NIK'],
                'NoSurat'      => $validated['NoSurat'],
                'Dokumen_IO'   => $project->dokumen_io ?? null,
                'Jabatan'      => $validated['Jabatan'],
                'Periodeawal'  => $validated['Periodeawal'],
                'Periodeakhir' => $validated['Periodeakhir'],
                'Bobot'        => $validated['Bobot'],
                'Status'       => $validated['Status'],
                'Keterangan'   => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penugasan berhasil disimpan',
                'data'    => $penugasan,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing penugasan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
        UPDATE (AJAX)
    ========================== */

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id'           => 'required|integer',
            'Jabatan'      => 'required|string|max:30',
            'Periodeawal'  => 'required|date',
            'Periodeakhir' => 'required|date',
            'Bobot'        => 'required|numeric|min:0.01|max:100',
            'Status'       => 'required|in:A,N',
        ]);

        if (Carbon::parse($validated['Periodeawal'])->gt(Carbon::parse($validated['Periodeakhir']))) {
            return response()->json([
                'success' => false,
                'message' => 'Periode Awal tidak boleh lebih besar dari Periode Akhir'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $penugasan = Penugasan::findOrFail($validated['id']);

            $newStart = Carbon::parse($validated['Periodeawal']);
            $newEnd   = Carbon::parse($validated['Periodeakhir']);

            // Overlap check: same CC + NIK + Jabatan (ci), exclude self
            $overlap = Penugasan::where('cost_center', $penugasan->cost_center)
                ->where('NIK', $penugasan->NIK)
                ->whereRaw('LOWER(Jabatan) = ?', [strtolower($validated['Jabatan'])])
                ->where('id', '!=', $penugasan->id)
                ->whereDate('Periodeawal', '<=', $newEnd)
                ->whereDate('Periodeakhir', '>=', $newStart)
                ->first();

            if ($overlap) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'overlap' => true,
                    'message' => 'Periode bersinggungan dengan data yang sudah ada',
                    'existing' => [
                        'jabatan'       => $overlap->Jabatan,
                        'periode_awal'  => $overlap->Periodeawal ? $overlap->Periodeawal->format('Y-m-d') : null,
                        'periode_akhir' => $overlap->Periodeakhir ? $overlap->Periodeakhir->format('Y-m-d') : null,
                        'bobot'         => $overlap->Bobot,
                        'status'        => $overlap->Status,
                    ],
                ], 422);
            }

            $penugasan->update([
                'Jabatan'      => $validated['Jabatan'],
                'Periodeawal'  => $validated['Periodeawal'],
                'Periodeakhir' => $validated['Periodeakhir'],
                'Bobot'        => $validated['Bobot'],
                'Status'       => $validated['Status'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penugasan berhasil diperbarui',
                'data'    => $penugasan,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating penugasan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
        DESTROY (AJAX)
    ========================== */

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer',
        ]);

        try {
            $penugasan = Penugasan::findOrFail($validated['id']);
            $penugasan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data penugasan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting penugasan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /* =========================
        UPLOAD EXCEL (AJAX 2-phase)
    ========================== */

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file'           => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'id_penugasan'   => 'required|string|max:10',
        ], [
            'file.required'         => 'File wajib diunggah',
            'file.mimes'            => 'Format file harus xlsx, xls, atau csv',
            'file.max'              => 'Ukuran file maksimal 5MB',
            'id_penugasan.required' => 'Pilih ID Penugasan terlebih dahulu',
        ]);

        $idPenugasan    = $request->input('id_penugasan');
        $confirmReplace = $request->boolean('confirm_replace', false);

        // Resolve header
        $header = HeaderPenugasan::where('IDPenugasan', $idPenugasan)->first();
        if (!$header) {
            return response()->json([
                'success' => false,
                'message' => "ID Penugasan '{$idPenugasan}' tidak ditemukan",
            ], 422);
        }
        $headerCC     = $header->cost_center;
        $headerNoSurat = $header->NoSurat;
        $project       = HistoryProyek::where('id_project', $header->id_project)
            ->where('norut', $header->no_urut)
            ->first();

        try {
            $file        = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, false, true);

            $parsedRows = [];
            $errors     = [];
            $duplicates = [];
            $overlaps   = [];

            foreach ($rows as $index => $row) {
                if ($index === 1) continue; 

                $nik           = trim($row['A'] ?? '');
                $jabatan       = trim($row['B'] ?? '');
                $periodeAwalR  = trim($row['C'] ?? '');
                $periodeAkhirR = trim($row['D'] ?? '');
                $bobotRaw      = $row['E'] ?? '';
                $statusRaw     = trim($row['F'] ?? '');

                if (empty($nik) && empty($jabatan)) continue;

                if (empty($nik)) {
                    $errors[] = "Baris {$index}: NIK wajib diisi";
                    continue;
                }
                if (empty($jabatan)) {
                    $errors[] = "Baris {$index}: Jabatan wajib diisi";
                    continue;
                }
                if (empty($periodeAwalR)) {
                    $errors[] = "Baris {$index}: Periode Awal wajib diisi";
                    continue;
                }
                if (empty($periodeAkhirR)) {
                    $errors[] = "Baris {$index}: Periode Akhir wajib diisi";
                    continue;
                }

                // Validate Bobot
                if (!is_numeric($bobotRaw)) {
                    $errors[] = "Baris {$index}: Bobot harus berupa angka (0.01-100), ditemukan: '{$bobotRaw}'";
                    continue;
                }
                $bobot = round(floatval($bobotRaw), 2);
                if ($bobot <= 0) {
                    $errors[] = "Baris {$index}: Bobot tidak boleh 0, ditemukan: {$bobot}";
                    continue;
                }
                if ($bobot > 100) {
                    $errors[] = "Baris {$index}: Bobot tidak boleh lebih dari 100, ditemukan: {$bobot}";
                    continue;
                }

                // Validate Status
                if (empty($statusRaw) || !in_array(strtoupper($statusRaw), ['A', 'N'])) {
                    $errors[] = "Baris {$index}: Status harus 'A' atau 'N', ditemukan: '{$statusRaw}'";
                    continue;
                }
                $status = strtoupper($statusRaw);

                try {
                    $periodeAwal = $this->parseExcelDate($periodeAwalR);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$index}: Periode Awal tidak valid ('{$periodeAwalR}')";
                    continue;
                }
                try {
                    $periodeAkhir = $this->parseExcelDate($periodeAkhirR);
                } catch (\Exception $e) {
                    $errors[] = "Baris {$index}: Periode Akhir tidak valid ('{$periodeAkhirR}')";
                    continue;
                }

                if ($periodeAwal->gt($periodeAkhir)) {
                    $errors[] = "Baris {$index}: Periode Awal lebih besar dari Periode Akhir";
                    continue;
                }

                if (!Karyawan::where('nik', $nik)->exists()) {
                    $errors[] = "Baris {$index}: NIK '{$nik}' tidak ditemukan di data karyawan";
                    continue;
                }

                // 1. Exact duplicate check
                $existing = Penugasan::where('cost_center', $headerCC)
                    ->where('NIK', $nik)
                    ->whereDate('Periodeawal', $periodeAwal->format('Y-m-d'))
                    ->whereDate('Periodeakhir', $periodeAkhir->format('Y-m-d'))
                    ->whereRaw('LOWER(Jabatan) = ?', [strtolower($jabatan)])
                    ->first();

                if ($existing) {
                    $nama = Karyawan::where('nik', $nik)->value('nama') ?? '-';
                    $duplicates[] = [
                        'row'            => $index,
                        'cost_center'    => $headerCC,
                        'nik'            => $nik,
                        'nama'           => $nama,
                        'jabatan'        => $jabatan,
                        'periode_awal'   => $periodeAwal->format('d/m/Y'),
                        'periode_akhir'  => $periodeAkhir->format('d/m/Y'),
                        'existing_bobot' => $existing->Bobot,
                        'existing_status' => $existing->Status,
                    ];
                } else {
                    // 2. Overlap check: same CC + NIK + Jabatan (ci), period overlaps
                    $overlapRow = Penugasan::where('cost_center', $headerCC)
                        ->where('NIK', $nik)
                        ->whereRaw('LOWER(Jabatan) = ?', [strtolower($jabatan)])
                        ->whereDate('Periodeawal', '<=', $periodeAkhir->format('Y-m-d'))
                        ->whereDate('Periodeakhir', '>=', $periodeAwal->format('Y-m-d'))
                        ->first();

                    if ($overlapRow) {
                        $nama = Karyawan::where('nik', $nik)->value('nama') ?? '-';
                        $errors[] = "Baris {$index}: Periode bersinggungan — NIK '{$nik}' jabatan '{$jabatan}' sudah ada periode " .
                            ($overlapRow->Periodeawal ? $overlapRow->Periodeawal->format('d/m/Y') : '?') . ' s/d ' .
                            ($overlapRow->Periodeakhir ? $overlapRow->Periodeakhir->format('d/m/Y') : '?');
                        continue;
                    }
                }

                $parsedRows[] = [
                    'index'         => $index,
                    'cost_center'   => $headerCC,
                    'nik'           => $nik,
                    'jabatan'       => $jabatan,
                    'periode_awal'  => $periodeAwal,
                    'periode_akhir' => $periodeAkhir,
                    'bobot'         => $bobot,
                    'status'        => $status,
                    'dok_io'        => $project->dokumen_io ?? null,
                    'has_existing'  => $existing ? true : false,
                    'existing_id'   => $existing ? $existing->id : null,
                ];
            }

            // Phase 1: if duplicates found and not confirmed, return preview
            if (count($duplicates) > 0 && !$confirmReplace) {
                return response()->json([
                    'success'         => false,
                    'has_duplicates'  => true,
                    'duplicates'      => $duplicates,
                    'total_rows'      => count($parsedRows),
                    'new_rows'        => count($parsedRows) - count($duplicates),
                    'duplicate_count' => count($duplicates),
                    'errors'          => $errors,
                    'message'         => 'Ditemukan ' . count($duplicates) . ' data duplikat. Konfirmasi untuk mengganti.',
                ], 409);
            }

            // Phase 2: import all into the selected header
            DB::beginTransaction();
            $imported = 0;
            $norutCounter = Penugasan::where('IDPenugasan', $idPenugasan)->max('Norut') ?? 0;

            foreach ($parsedRows as $parsed) {
                if ($parsed['has_existing']) {
                    Penugasan::where('id', $parsed['existing_id'])->update([
                        'Bobot'  => $parsed['bobot'],
                        'Status' => $parsed['status'],
                    ]);
                } else {
                    $norutCounter++;
                    Penugasan::create([
                        'IDPenugasan'  => $idPenugasan,
                        'cost_center'  => $parsed['cost_center'],
                        'Norut'        => $norutCounter,
                        'NIK'          => $parsed['nik'],
                        'NoSurat'      => $headerNoSurat,
                        'Dokumen_IO'   => $parsed['dok_io'],
                        'Jabatan'      => $parsed['jabatan'],
                        'Periodeawal'  => $parsed['periode_awal']->format('Y-m-d'),
                        'Periodeakhir' => $parsed['periode_akhir']->format('Y-m-d'),
                        'Bobot'        => $parsed['bobot'],
                        'Status'       => $parsed['status'],
                        'Keterangan'   => null,
                    ]);
                }
                $imported++;
            }

            DB::commit();

            if ($imported === 0 && count($errors) > 0) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Tidak ada data yang berhasil diimpor. ' . count($errors) . ' baris gagal.',
                    'imported' => 0,
                    'errors'   => $errors,
                ], 422);
            }

            $replacedCount = count($duplicates);
            $message = "{$imported} data berhasil diimpor.";
            if ($replacedCount > 0) $message .= " ({$replacedCount} data diganti).";
            if (count($errors) > 0) $message .= " " . count($errors) . " baris gagal.";

            return response()->json([
                'success'        => true,
                'has_errors'     => count($errors) > 0,
                'has_duplicates' => false,
                'message'        => $message,
                'imported'       => $imported,
                'replaced'       => $replacedCount,
                'errors'         => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading penugasan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data. Pastikan format file sesuai template.'
            ], 500);
        }
    }

    /* =========================
        DOWNLOAD TEMPLATE (XLSX)
    ========================== */

    public function downloadTemplate(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Penugasan');

        $headers = [
            'A1' => 'NIK',
            'B1' => 'Jabatan',
            'C1' => 'Periode Awal',
            'D1' => 'Periode Akhir',
            'E1' => 'Bobot (%)',
            'F1' => 'Status (A/N)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        $sampleData = [
            ['3000100', 'Project Manager', '01/01/2026', '31/03/2026', 100,  'A'],
            ['3090546', 'Engineer',        '01/01/2026', '31/03/2026', 33.5, 'N'],
            ['3000100', 'Supervisor',      '01/04/2026', '30/06/2026', 0.5,  'A'],
        ];

        $row = 2;
        foreach ($sampleData as $data) {
            $sheet->setCellValue("A{$row}", $data[0]);
            $sheet->setCellValue("B{$row}", $data[1]);
            $sheet->setCellValue("C{$row}", $data[2]);
            $sheet->setCellValue("D{$row}", $data[3]);
            $sheet->setCellValue("E{$row}", $data[4]);
            $sheet->setCellValue("F{$row}", $data[5]);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $lastRow = $row - 1;
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle("A1:F{$lastRow}")->applyFromArray($styleArray);

        $filename = 'template_penugasan.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $temp = tempnam(sys_get_temp_dir(), 'penugasan');
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /* =========================
        PARSE EXCEL DATE
    ========================== */

    private function parseExcelDate($value): Carbon
    {
        if ($value === null || $value === '') {
            throw new \Exception('Date value is empty');
        }

        if (is_numeric($value) && (float)$value > 100) {
            return Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)
            );
        }

        $value = trim((string)$value);
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->startOfDay();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Exception $e) {
            throw new \Exception("Cannot parse date: {$value}");
        }
    }
}
