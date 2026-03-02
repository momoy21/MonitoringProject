<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataProyek;
use App\Models\HistoryProyek;
use App\Models\Konsumen;
use App\Models\DataPeluang;
use App\Models\BidangJasa;
use App\Models\KondisiProyek;
use App\Models\MasterManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DataProyekController extends Controller
{
    /**
     * Display a listing of all projects.
     * Each row represents one unique project (by id_project).
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        // Get current user
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Query semua proyek dengan relationships, tidak di-group by cost_center
        $query = DataProyek::with(['konsumen', 'bidangJasa', 'kondisiProyek', 'manager']);

        // Filter by user's allowed bidang jasa if user is PM
        if ($user) {
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
            if (!$isSuperAdmin) {
                $allowedIds = $user->getAllowedBidangJasaIds();
                if (!empty($allowedIds)) {
                    $query->whereIn('id_bidjasa', $allowedIds);
                }
            }
        }

        // Search handle - mengikuti pattern konsumen
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cost_center', 'LIKE', "%{$search}%")
                  ->orWhere('id_project', 'LIKE', "%{$search}%")
                  ->orWhere('namaproject', 'LIKE', "%{$search}%")
                  ->orWhereHas('konsumen', function($konsumenQuery) use ($search) {
                      $konsumenQuery->where('konsumen', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Order by created_at DESC (terbaru di atas)
        $projects = $query->orderBy('created_at', 'DESC')->paginate($perPage);

        // Format each project for display - NO GROUPING, NO OVERRIDE
        foreach ($projects as $project) {
            // Format nilai proyek
            if ($project->nilai_proyek && $project->nilai_proyek > 0) {
                $formattedNumber = number_format((float)$project->nilai_proyek, 0, ',', '.');
                $project->nilai_proyek_formatted = '<div class="d-flex justify-content-between align-items-center" style="gap: 0.5rem;"><span>Rp</span><span>' . $formattedNumber . '</span></div>';
            } else {
                $project->nilai_proyek_formatted = '<div class="text-center text-muted">-</div>';
            }

            // Add status attributes
            $project->status_text = $this->getStatusText($project->status);
            $project->status_badge = $this->getStatusBadge($project->status);
        }

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            // Format data for AJAX response - setiap project adalah row terpisah
            $formattedData = $projects->getCollection()->map(function ($project) {
                return [
                    'id_project' => $project->id_project,
                    'cost_center' => $project->cost_center,
                    'namaproject' => $project->namaproject,
                    'id_konsumen' => $project->id_konsumen,
                    'no_kontrak' => $project->no_kontrak,
                    'nilai_proyek' => $project->nilai_proyek,
                    'tgl_kontrak' => $project->tgl_kontrak,
                    'start_kontrak' => $project->start_kontrak,
                    'finish_kontrak' => $project->finish_kontrak,
                    'status' => $project->status,
                    'status_text' => $this->getStatusText($project->status),
                    'status_badge' => $this->getStatusBadge($project->status),
                    'konsumen' => $project->konsumen,
                    'bidangJasa' => $project->bidangJasa,
                    'nilai_proyek_formatted' => $project->nilai_proyek && $project->nilai_proyek > 0
                        ? '<div class="d-flex justify-content-between align-items-center" style="gap: 0.5rem;"><span>Rp</span><span>' . number_format((float)$project->nilai_proyek, 0, ',', '.') . '</span></div>'
                        : '<div class="text-center text-muted">-</div>'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem()
                ]
            ]);
        }

        return view('dataproyek.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if this is for a specific cost center (adding to history)
        $costCenter = request('cost_center');

        // Get current user
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Get bidang jasa with filtering based on user
        $bidangJasaQuery = BidangJasa::active()->orderBy('desc_bidjasa');

        // Filter by user's allowed bidang jasa if user is PM
        if ($user) {
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
            if (!$isSuperAdmin) {
                $allowedIds = $user->getAllowedBidangJasaIds();
                if (!empty($allowedIds)) {
                    $bidangJasaQuery->whereIn('id_bidjasa', $allowedIds);
                }
            }
        }

        $bidangJasa = $bidangJasaQuery->get();

        $data = [
            'konsumen' => Konsumen::where('status', 'A')->orderBy('konsumen')->get(),
            'dataPeluang' => DataPeluang::whereIn('status', ['D', 'I'])->orderBy('peluang')->get(),
            'bidangJasa' => $bidangJasa,
            'kondisiProyek' => KondisiProyek::active()->orderBy('desc_kondisi_proyek')->get(),
            'managers' => MasterManager::with('divisi')->where('status', 'A')->orderBy('nama')->get(),
            'costCenter' => $costCenter,
            'jarakOptions' => [
                1 => 'Jarak 5KM - 10KM',
                2 => 'Jarak 21KM - 30KM',
                3 => 'Jarak 31KM - 40KM',
                4 => 'Jarak 41KM - 50KM',
                5 => 'Jarak 51KM - 60KM',
                6 => 'Jarak 11KM - 20KM'
            ],
            'statusOptions' => [
                'O' => 'Open',
                'I' => 'InProgress',
                'C' => 'Close',
                'P' => 'Pending',
                'F' => 'Finish Pekerjaan'
            ],
            'keteranganOptions' => [
                '1' => 'Kontrak Induk',
                '2' => 'Bukan Kontrak Induk'
            ],
            'defaultKeterangan' => '2'
        ];

        return view('dataproyek.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        // Check if cost center already exists in data_proyek table
        $existingProject = DataProyek::where('cost_center', $request->cost_center)->first();

        if ($existingProject) {
            // Redirect to existing project's history page with warning
            return redirect()->route('dataproyek.show', $existingProject->id_project)
                ->with('warning', "Cost Center '{$request->cost_center}' sudah digunakan oleh Project ID: {$existingProject->id_project}. Anda dapat menambah history pada proyek tersebut.");
        }

        // Validation rules
        $isAddingToHistory = $request->has('add_to_history') && ($request->add_to_history == '1' || $request->add_to_history === true);

        $rules = [
            'dokumen_io' => 'nullable|numeric|digits:9',
            'cost_center' => 'required|string|max:9|regex:/^[A-Za-z0-9]+$/|unique:data_proyek,cost_center',
            'namaproject' => 'required|string',
            'id_datapeluang' => 'nullable|exists:data_peluang,id_datapeluang',
            'lokasi_proyek' => 'nullable|string|max:100',
            'jarak_lokasi' => 'nullable|integer|in:1,2,3,4,5,6',
            'no_kontrak' => 'nullable|string|max:100',
            'tgl_pengakuan' => 'nullable|date',
            'tgl_kontrak' => 'nullable|date',
            'start_kontrak' => 'required|date',
            'finish_kontrak' => 'required|date|after:start_kontrak',
            'tgl_expire' => 'nullable|date',
            'penanggung_jawab' => 'nullable|exists:master_manager,nik',
            'kode_divisi' => 'nullable|exists:master_divisi,kode_divisi',
            'nilai_proyek' => 'nullable|numeric|min:0',
            'status' => 'required|in:O,I,C,P,F',
            'dokumen_kontrak' => 'nullable|file|mimes:docx,doc,pdf,xlsx,xls,pptx,ppt,jpg,jpeg,png|max:25600',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'id_bidjasa' => 'required|exists:bidangjasa,id_bidjasa',
            'id_kondisi_proyek' => 'required|exists:kondisiproyek,id_kondisi_proyek',
            'keterangan' => 'required|in:1,2'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate NEW ID Project
            $idProject = $this->generateIdProject();

            // Handle file upload
            $dokumenPath = null;
            if ($request->hasFile('dokumen_kontrak')) {
                $dokumenPath = $this->generateUniqueFilename($request->file('dokumen_kontrak'), 'dokumen_proyek');
            }

            // Clean currency values
            $data = $request->except(['dokumen_kontrak', 'add_to_history', 'parent_id_project']);

            if (isset($data['nilai_proyek']) && $data['nilai_proyek']) {
                $data['nilai_proyek'] = preg_replace('/[^\d]/', '', $data['nilai_proyek']);
            }

            $data['id_project'] = $idProject;
            $data['dokumen_path'] = $dokumenPath;

            if ($isAddingToHistory) {
                // CREATE-HISTORY: Only save to history_proyek table
                HistoryProyek::createWithAutoNorut($data);
            } else {
                // REGULAR CREATE from index page
                if ($request->keterangan == '1') {
                    // Kontrak Induk: save to data_proyek only
                    $newProject = DataProyek::create($data);
                } elseif ($request->keterangan == '2') {
                    // Bukan Kontrak Induk: save to BOTH data_proyek AND history_proyek
                    $newProject = DataProyek::create($data);

                    // Also create entry in history_proyek with norut=1
                    $historyData = $data;
                    $historyData['keterangan'] = 'Proyek Awal'; // text bebas untuk history

                    // FIX: Copy dokumen path dari data proyek ke history proyek
                    if (isset($data['dokumen_path']) && $data['dokumen_path']) {
                        $historyData['dokumen_path'] = $data['dokumen_path'];
                    }

                    HistoryProyek::createWithAutoNorut($historyData);
                }
            }

            DB::commit();

            // Redirect based on context
            if ($isAddingToHistory) {
                $costCenter = $request->cost_center;
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Data proyek berhasil ditambahkan ke history.',
                        'redirect_url' => route('dataproyek.show', $costCenter)
                    ]);
                }

                return redirect()->route('dataproyek.show', $costCenter)
                    ->with('success', 'Data proyek berhasil ditambahkan ke history.');
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Data proyek berhasil disimpan.',
                        'redirect_url' => route('dataproyek.index')
                    ]);
                }

                return redirect()->route('dataproyek.index')
                    ->with('success', 'Data proyek berhasil disimpan.');
            }

        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }


    /**
     * Store history project specifically (dedicated method for adding to history)
     */
    public function storeHistory(Request $request, string $idProject)
    {
        // Validation rules for history projects
        $rules = [
            'dokumen_io' => 'nullable|numeric|digits:9',
            'cost_center' => 'nullable|string|max:9', // From parent project
            'namaproject' => 'required|string',
            'no_kontrak' => 'nullable|string|max:100',
            'tgl_pengakuan' => 'nullable|date',
            'tgl_kontrak' => 'nullable|date',
            'start_kontrak' => 'required|date',
            'finish_kontrak' => 'required|date|after:start_kontrak',
            'tgl_expire' => 'nullable|date',
            'penanggung_jawab' => 'nullable|exists:master_manager,nik',
            'kode_divisi' => 'nullable|exists:master_divisi,kode_divisi',
            'nilai_proyek' => 'nullable|numeric|min:0',
            'status' => 'required|in:O,I,C,P,F',
            'dokumen_kontrak' => 'nullable|file|mimes:docx,doc,pdf,xlsx,xls,pptx,ppt,jpg,jpeg,png|max:25600',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'id_bidjasa' => 'required|exists:bidangjasa,id_bidjasa',
            'id_kondisi_proyek' => 'required|exists:kondisiproyek,id_kondisi_proyek',
            'keterangan' => 'nullable|string|max:255', // Field bebas untuk keterangan
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // Handle AJAX request differently
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Get parent project to retrieve cost_center
            $parentProject = DataProyek::where('id_project', $idProject)->first();
            if (!$parentProject) {
                throw new \Exception('Parent project not found');
            }

            // Handle file upload dengan unique filename
            $dokumenPath = null;
            if ($request->hasFile('dokumen_kontrak')) {
                $dokumenPath = $this->generateUniqueFilename($request->file('dokumen_kontrak'), 'dokumen_proyek');
            }

            // Prepare data
            $data = $request->except(['dokumen_kontrak', '_token']);
            $data['cost_center'] = $parentProject->cost_center; // Use parent's cost center
            $data['id_project'] = $idProject; // Use SAME id_project as parent
            $data['dokumen_path'] = $dokumenPath;
            // Keterangan sudah diambil dari form (text bebas), tidak perlu di-set ulang

            // Clean currency values
            if (isset($data['nilai_proyek']) && $data['nilai_proyek']) {
                $originalValue = $data['nilai_proyek'];
                $data['nilai_proyek'] = preg_replace('/[^\d]/', '', $data['nilai_proyek']);
            }

            // Save to history table only
            $newHistoryProject = HistoryProyek::createWithAutoNorut($data);

            // Ensure norut sequence is correct for this id_project (not cost_center)
            HistoryProyek::fixNorutSequence($idProject);

            // Note: Don't use refresh() with composite key, it causes "Illegal offset type" error
            // The record is already saved and norut sequence is fixed
            DB::commit();

            $redirectUrl = route('dataproyek.show', $idProject);
            // Handle AJAX request differently
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data proyek berhasil ditambahkan ke history.',
                    'redirect_url' => "/dataproyek/{$idProject}"
                ]);
            }

            return redirect("/dataproyek/{$idProject}")
                ->with('success', 'Data proyek berhasil ditambahkan ke history.');

        } catch (\Exception $e) {
            DB::rollback();
            // Handle AJAX request differently
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource (Detail Project with History).
     * Shows main project and all history records based on id_project.
     */
    public function show(Request $request, string $idProject)
    {
        $perPage = $request->get('per_page', 15);

        // Get main project by id_project
        $mainProject = DataProyek::where('id_project', $idProject)
            ->with(['konsumen', 'bidangJasa', 'kondisiProyek', 'manager'])
            ->first();

        if (!$mainProject) {
            abort(404, 'Proyek tidak ditemukan.');
        }

        // Get history projects with the SAME id_project (not cost_center)
        $historyProyekQuery = HistoryProyek::where('id_project', $idProject)
            ->with(['konsumen', 'bidangJasa', 'kondisiProyek', 'manager'])
            ->orderBy('norut', 'desc');

        // Search handle
        if ($request->filled('search')) {
            $search = $request->search;
            $historyProyekQuery->where(function($q) use ($search) {
                $q->where('no_kontrak', 'LIKE', "%{$search}%")
                  ->orWhere('namaproject', 'LIKE', "%{$search}%");
            });
        }

        $historyProjects = $historyProyekQuery->paginate($perPage);

        foreach ($historyProjects as $project) {
            if (!isset($project->nilai_proyek_formatted)) {
                $project->nilai_proyek_formatted = $project->getNilaiProyekFormattedAttribute();
            }
            if (!isset($project->status_text)) {
                $project->status_text = $project->getStatusTextAttribute();
            }
            if (!isset($project->status_badge)) {
                $project->status_badge = $project->getStatusBadgeAttribute();
            }
        }

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            $formattedData = $historyProjects->getCollection()->map(function ($project) {
                return [
                    'id' => $project->id,
                    'norut' => $project->norut,
                    'id_project' => $project->id_project,
                    'namaproject' => $project->namaproject,
                    'no_kontrak' => $project->no_kontrak,
                    'nilai_proyek' => $project->nilai_proyek,
                    'tgl_kontrak' => $project->tgl_kontrak ? $project->tgl_kontrak->format('d/m/Y') : null,
                    'start_kontrak' => $project->start_kontrak ? $project->start_kontrak->format('d/m/Y') : null,
                    'finish_kontrak' => $project->finish_kontrak ? $project->finish_kontrak->format('d/m/Y') : null,
                    'status' => $project->status,
                    'status_text' => $project->status_text,
                    'status_badge' => $project->status_badge,
                    'nilai_proyek_formatted' => $project->nilai_proyek_formatted
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $historyProjects->currentPage(),
                    'last_page' => $historyProjects->lastPage(),
                    'per_page' => $historyProjects->perPage(),
                    'total' => $historyProjects->total(),
                    'from' => $historyProjects->firstItem(),
                    'to' => $historyProjects->lastItem()
                ]
            ]);
        }

        return view('dataproyek.show', compact('mainProject', 'historyProjects', 'idProject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $idProject)
    {
        // Try to find in main table first, then history
        $project = DataProyek::with('divisi')->where('id_project', $idProject)->first();
        $isHistory = false;

        if (!$project) {
            $project = HistoryProyek::with('divisi')->where('id_project', $idProject)->first();
            $isHistory = true;
        }

        if (!$project) {
            abort(404, 'Proyek tidak ditemukan.');
        }

        // For history projects, use edit-history view with parent project data
        if ($isHistory) {
            // Get parent project by id_project (not cost_center)
            // Each history belongs to a specific id_project
            $parentProject = DataProyek::where('id_project', $project->id_project)->first();

            if (!$parentProject) {
                // If no project found in data_proyek, this history might be orphaned
                // Just use the history project data itself as reference
                $parentProject = $project;
            }

            // Get jarak display text
            $jarakOptions = [
                1 => 'Jarak 5KM - 10KM',
                2 => 'Jarak 21KM - 30KM',
                3 => 'Jarak 31KM - 40KM',
                4 => 'Jarak 41KM - 50KM',
                5 => 'Jarak 51KM - 60KM',
                6 => 'Jarak 11KM - 20KM'
            ];
            $jarakDisplay = isset($parentProject->jarak_lokasi) && isset($jarakOptions[$parentProject->jarak_lokasi])
                ? $jarakOptions[$parentProject->jarak_lokasi]
                : 'Belum diisi';

            $data = [
                'project' => $project,
                'parentProject' => $parentProject,
                'jarakDisplay' => $jarakDisplay,
                'managers' => MasterManager::with('divisi')->where('status', 'A')->orderBy('nama')->get(),
                'statusOptions' => [
                    'O' => 'Open',
                    'I' => 'InProgress',
                    'C' => 'Close',
                    'P' => 'Pending',
                    'F' => 'Finish Pekerjaan'
                ]
            ];

            return view('dataproyek.edit-history', $data);
        }

        // For regular projects, use normal edit view
        // Get current user
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Get bidang jasa with filtering based on user
        $bidangJasaQuery = BidangJasa::active()->orderBy('desc_bidjasa');

        // Filter by user's allowed bidang jasa if user is PM
        if ($user) {
            $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('Super Admin');
            if (!$isSuperAdmin) {
                $allowedIds = $user->getAllowedBidangJasaIds();
                if (!empty($allowedIds)) {
                    $bidangJasaQuery->whereIn('id_bidjasa', $allowedIds);
                }
            }
        }

        $data = [
            'project' => $project,
            'isHistory' => $isHistory,
            'konsumen' => Konsumen::where('status', 'A')->orderBy('konsumen')->get(),
            'dataPeluang' => DataPeluang::whereIn('status', ['D', 'I'])->orderBy('peluang')->get(),
            'bidangJasa' => $bidangJasaQuery->get(),
            'kondisiProyek' => KondisiProyek::active()->orderBy('desc_kondisi_proyek')->get(),
            'managers' => MasterManager::with('divisi')->where('status', 'A')->orderBy('nama')->get(),
            'jarakOptions' => [
                1 => 'Jarak 5KM - 10KM',
                2 => 'Jarak 21KM - 30KM',
                3 => 'Jarak 31KM - 40KM',
                4 => 'Jarak 41KM - 50KM',
                5 => 'Jarak 51KM - 60KM',
                6 => 'Jarak 11KM - 20KM'
            ],
            'statusOptions' => [
                'O' => 'Open',
                'I' => 'InProgress',
                'C' => 'Close',
                'P' => 'Pending',
                'F' => 'Finish Pekerjaan'
            ],
            'keteranganOptions' => [
                '1' => 'Kontrak Induk',
                '2' => 'Bukan Kontrak Induk'
            ]
        ];

        return view('dataproyek.edit', $data);
    }

    /**
     * Show the form for editing history project (by history ID).
     */
    public function editHistory(Request $request, string $idProject, int $norut)
    {
        // Find history project by composite key (norut, id_project)
        $project = HistoryProyek::with('divisi')
            ->where('id_project', $idProject)
            ->where('norut', $norut)
            ->first();

        if (!$project) {
            abort(404, 'History proyek tidak ditemukan.');
        }

        // Get parent project by id_project
        $parentProject = DataProyek::where('id_project', $project->id_project)
            ->with(['konsumen', 'bidangJasa', 'kondisiProyek', 'manager', 'dataPeluang'])
            ->first();

        if (!$parentProject) {
            // If no project found in data_proyek, use history project data as reference
            $parentProject = $project;
        }

        // Get jarak display text
        $jarakOptions = [
            1 => 'Jarak 5KM - 10KM',
            2 => 'Jarak 21KM - 30KM',
            3 => 'Jarak 31KM - 40KM',
            4 => 'Jarak 41KM - 50KM',
            5 => 'Jarak 51KM - 60KM',
            6 => 'Jarak 11KM - 20KM'
        ];
        $jarakDisplay = isset($parentProject->jarak_lokasi) && isset($jarakOptions[$parentProject->jarak_lokasi])
            ? $jarakOptions[$parentProject->jarak_lokasi]
            : 'Belum diisi';

        $data = [
            'project' => $project,
            'parentProject' => $parentProject,
            'jarakDisplay' => $jarakDisplay,
            'managers' => MasterManager::with('divisi')->where('status', 'A')->orderBy('nama')->get(),
            'statusOptions' => [
                'O' => 'Open',
                'I' => 'InProgress',
                'C' => 'Close',
                'P' => 'Pending',
                'F' => 'Finish Pekerjaan'
            ]
        ];

        return view('dataproyek.edit-history', $data);
    }

    /**
     * Update history project by its ID (primary key).
     */
     public function updateHistory(Request $request, string $idProject, int $norut)
    {
        // Convert date format from dd/mm/yyyy to Y-m-d before validation
        $dateFields = ['tgl_pengakuan', 'tgl_kontrak', 'start_kontrak', 'finish_kontrak', 'tgl_expire'];
        foreach ($dateFields as $field) {
            if ($request->has($field) && $request->input($field)) {
                $dateValue = $request->input($field);
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                    $parts = explode('/', $dateValue);
                    $convertedDate = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                    $request->merge([$field => $convertedDate]);
                }
            }
        }

        // Validation rules
        $validator = Validator::make($request->all(), [
            'dokumen_io' => 'nullable|numeric|digits:9',
            'cost_center' => 'required|string|max:9|regex:/^[A-Za-z0-9]+$/',
            'namaproject' => 'required|string',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'id_datapeluang' => 'nullable|exists:data_peluang,id_datapeluang',
            'id_bidjasa' => 'required|exists:bidangjasa,id_bidjasa',
            'lokasi_proyek' => 'nullable|string|max:100',
            'jarak_lokasi' => 'nullable|integer|in:1,2,3,4,5,6',
            'id_kondisi_proyek' => 'required|exists:kondisiproyek,id_kondisi_proyek',
            'no_kontrak' => 'nullable|string|max:100',
            'tgl_pengakuan' => 'nullable|date',
            'tgl_kontrak' => 'nullable|date',
            'start_kontrak' => 'required|date',
            'finish_kontrak' => 'required|date|after:start_kontrak',
            'tgl_expire' => 'nullable|date',
            'penanggung_jawab' => 'nullable|exists:master_manager,nik',
            'nilai_proyek' => 'nullable|numeric|min:0',
            'status' => 'required|in:O,I,C,P,F',
            'keterangan' => 'nullable|string|max:255',
            'dokumen_kontrak' => 'nullable|file|mimes:docx,doc,pdf,xlsx,xls,pptx,ppt,jpg,jpeg,png|max:25600'
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors()->toArray()
                ], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Find history project by composite key
            $project = HistoryProyek::where('id_project', $idProject)
                ->where('norut', $norut)
                ->first();

            if (!$project) {
                throw new \Exception('History proyek tidak ditemukan.');
            }

            // Handle file upload
            $dokumenPath = $project->dokumen_path;
            if ($request->hasFile('dokumen_kontrak')) {
                // FIX: Ensure dokumen_path is a string before checking storage
                $oldPath = is_array($project->dokumen_path)
                    ? ($project->dokumen_path[0] ?? null)
                    : $project->dokumen_path;

                // Delete old file if exists
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                // Upload new file
                $dokumenPath = $this->generateUniqueFilename($request->file('dokumen_kontrak'), 'dokumen_proyek');
            }

            // Prepare data for update
            $data = $request->except(['dokumen_kontrak', '_token', '_method']);

            // Clean currency value
            if (isset($data['nilai_proyek']) && $data['nilai_proyek']) {
                $cleanValue = preg_replace('/[^\d]/', '', $data['nilai_proyek']);
                $data['nilai_proyek'] = $cleanValue ?: null;
            }

            $data['dokumen_path'] = $dokumenPath;

            // Update history project
            $project->update($data);

            // Note: Don't use refresh() with composite key, it causes "Illegal offset type" error
            // The update is already applied to $project object automatically

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'History proyek berhasil diperbarui.',
                    'redirect_url' => route('dataproyek.show', $project->id_project)
                ]);
            }

            return redirect()->route('dataproyek.show', $project->id_project)
                ->with('success', 'History proyek berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui history proyek: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui history proyek: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $idProject)
    {
        // Log update operation start


        // Get current project
        $currentProject = DataProyek::where('id_project', $idProject)->first();
        if (!$currentProject) {
            return redirect()->back()->withErrors(['error' => 'Proyek tidak ditemukan.']);
        }

        // Check if cost center is being changed
        if ($request->cost_center !== $currentProject->cost_center) {
            return redirect()->back()
                ->withErrors(['cost_center' => 'Cost Center tidak dapat diubah setelah proyek dibuat.'])
                ->withInput();
        }

        // Debug dokumen_io specifically
        // Convert date format from dd/mm/yyyy to Y-m-d before validation
        $dateFields = ['tgl_pengakuan', 'tgl_kontrak', 'start_kontrak', 'finish_kontrak', 'tgl_expire'];
        foreach ($dateFields as $field) {
            if ($request->filled($field)) {
                $date = $request->input($field);
                // Convert dd/mm/yyyy to Y-m-d
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                    $parts = explode('/', $date);
                    $request->merge([
                        $field => $parts[2] . '-' . $parts[1] . '-' . $parts[0]
                    ]);
                }
            }
        }

        // Validation rules
        $validator = Validator::make($request->all(), [
            'dokumen_io' => 'nullable|numeric|digits:9',
            'cost_center' => 'required|string|max:9|regex:/^[A-Za-z0-9]+$/',
            'namaproject' => 'required|string',
            'id_konsumen' => 'required|exists:konsumen,id_konsumen',
            'id_datapeluang' => 'nullable|exists:data_peluang,id_datapeluang',
            'id_bidjasa' => 'required|exists:bidangjasa,id_bidjasa',
            'lokasi_proyek' => 'nullable|string|max:100',
            'jarak_lokasi' => 'nullable|integer|in:1,2,3,4,5,6',
            'id_kondisi_proyek' => 'required|exists:kondisiproyek,id_kondisi_proyek',
            'no_kontrak' => 'nullable|string|max:100',
            'tgl_pengakuan' => 'nullable|date',
            'tgl_kontrak' => 'nullable|date',
            'start_kontrak' => 'required|date',
            'finish_kontrak' => 'required|date|after:start_kontrak',
            'tgl_expire' => 'nullable|date',
            'penanggung_jawab' => 'nullable|exists:master_manager,nik',
            'kode_divisi' => 'nullable|exists:master_divisi,kode_divisi',
            'nilai_proyek' => 'nullable|numeric|min:0',
            'status' => 'required|in:O,I,C,P,F',
            'keterangan' => 'nullable|in:1,2',
            'dokumen_kontrak' => 'nullable|file|mimes:docx,doc,pdf,xlsx,xls,pptx,ppt,jpg,jpeg,png|max:25600'
        ], [
            'cost_center.required' => 'Cost center harus diisi',
            'namaproject.required' => 'Nama proyek harus diisi',
            'id_konsumen.required' => 'Konsumen harus dipilih',
            'id_bidjasa.required' => 'Bidang jasa harus dipilih',
            'id_kondisi_proyek.required' => 'Kondisi proyek harus dipilih',
            'start_kontrak.required' => 'Tanggal start kontrak harus diisi',
            'start_kontrak.date' => 'Format tanggal start kontrak tidak valid',
            'finish_kontrak.required' => 'Tanggal finish kontrak harus diisi',
            'finish_kontrak.date' => 'Format tanggal finish kontrak tidak valid',
            'finish_kontrak.after' => 'Tanggal finish kontrak harus setelah start kontrak',
            'status.required' => 'Status harus dipilih',
        ]);

        if ($validator->fails()) {
            $allErrors = $validator->errors()->toArray();
            // Log each field error individually for debugging
            foreach ($allErrors as $field => $messages) {
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Find the project
            $project = DataProyek::where('id_project', $idProject)->first();
            $isHistory = false;

            if (!$project) {
                $project = HistoryProyek::where('id_project', $idProject)->first();
                $isHistory = true;
            }

            if (!$project) {
                abort(404, 'Proyek tidak ditemukan.');
            }

            // Handle file upload dengan unique filename
            $dokumenPath = $project->dokumen_path;
            if ($request->hasFile('dokumen_kontrak')) {
                // ALWAYS delete old file if exists before uploading new one
                if ($project->dokumen_path && Storage::disk('public')->exists($project->dokumen_path)) {
                    Storage::disk('public')->delete($project->dokumen_path);
                }

                // Upload new file with unique filename
                $dokumenPath = $this->generateUniqueFilename($request->file('dokumen_kontrak'), 'dokumen_proyek');
            }

            // Clean currency values (following DataPeluang pattern)
            $data = $request->except(['dokumen_kontrak']);

            // Enhanced logging for currency debugging

            if (isset($data['nilai_proyek']) && $data['nilai_proyek']) {
                // Remove all non-numeric characters (dots, commas, spaces, currency symbols)
                $cleanValue = preg_replace('/[^\d]/', '', $data['nilai_proyek']);
                $data['nilai_proyek'] = $cleanValue;
            } else {
                $data['nilai_proyek'] = null;
            }
            $data['dokumen_path'] = $dokumenPath;
            // Update logic based on source table
            if (!$isHistory) {
                // Updating main table project - simple update, no history creation
                // IMPORTANT: Get OLD cost_center BEFORE update (for finding related histories)
                $oldCostCenter = $project->cost_center;

                // Update the main project
                $project->update($data);

                // Verify the update
                $project->refresh();

                // CASCADE UPDATE: Update only the initial history project (norut = 1)
                // Only cascade if keterangan = 2 (Bukan Kontrak Induk)
                if ($project->keterangan == 2) {
                    // For "Bukan Kontrak Induk", cascade ALL fields to history proyek with norut = 1
                    // History with norut = 1 is the auto-created history when data proyek was first created
                    // Manual histories (norut > 1) should NOT be updated

                    // Prepare cascade data (semua field kecuali yang tidak boleh di-cascade)
                    $cascadeFields = [
                        'cost_center' => $project->cost_center,
                        'dokumen_io' => $project->dokumen_io,
                        'namaproject' => $project->namaproject,
                        'id_konsumen' => $project->id_konsumen,
                        'id_datapeluang' => $project->id_datapeluang,
                        'id_bidjasa' => $project->id_bidjasa,
                        'lokasi_proyek' => $project->lokasi_proyek,
                        'jarak_lokasi' => $project->jarak_lokasi,
                        'id_kondisi_proyek' => $project->id_kondisi_proyek,
                        'no_kontrak' => $project->no_kontrak,
                        'tgl_pengakuan' => $project->tgl_pengakuan,
                        'tgl_kontrak' => $project->tgl_kontrak,
                        'start_kontrak' => $project->start_kontrak,
                        'finish_kontrak' => $project->finish_kontrak,
                        'tgl_expire' => $project->tgl_expire,
                        'penanggung_jawab' => $project->penanggung_jawab,
                        'nilai_proyek' => $project->nilai_proyek,
                        'status' => $project->status,
                        'keterangan' => $project->keterangan,
                        'dokumen_path' => $project->dokumen_path,
                    ];

                    // Find only the initial auto-created history project (norut = 1)
                    $initialHistory = HistoryProyek::where('id_project', $idProject)
                        ->where('norut', 1)
                        ->first();

                    if ($initialHistory) {
                        $initialHistory->update($cascadeFields);
                    }
                } else {
                    // For "Kontrak Induk" (keterangan = 1), only update cost_center if changed
                    // This maintains backward compatibility
                    if ($project->cost_center !== $oldCostCenter) {
                        $historyProjects = HistoryProyek::where('id_project', $idProject)->get();

                        if ($historyProjects->count() > 0) {
                            foreach ($historyProjects as $historyProject) {
                                $historyProject->update(['cost_center' => $project->cost_center]);
                            }
                        }
                    }
                }
            } else {
                // Updating history table project - direct update


                $project->update($data);
            }

            DB::commit();

            $redirectRoute = $isHistory ? 'dataproyek.show' : 'dataproyek.index';
            $redirectParams = $isHistory ? $project->cost_center : [];

            return redirect()->route($redirectRoute, $redirectParams)
                ->with('success', 'Data proyek berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();


            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (main data_proyek).
     */
    public function destroy(string $idProject)
    {
        try {
            DB::beginTransaction();

            // Find and delete from main table
            $project = DataProyek::where('id_project', $idProject)->first();

            if (!$project) {
                abort(404, 'Proyek tidak ditemukan.');
            }

            // Delete associated file
            if ($project->dokumen_path && Storage::disk('public')->exists($project->dokumen_path)) {
                Storage::disk('public')->delete($project->dokumen_path);
            }

            $project->delete();

            DB::commit();

            return redirect()->route('dataproyek.index')
                ->with('success', 'Data proyek berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove history proyek using composite key (id_project + norut)
     */
    public function destroyHistory(string $idProject, int $norut)
    {
        try {
            DB::beginTransaction();

            // Find history project by composite key
            $project = HistoryProyek::where('id_project', $idProject)
                ->where('norut', $norut)
                ->first();

            if (!$project) {
                abort(404, 'History proyek tidak ditemukan.');
            }

            // Delete associated file
            $dokumenPath = is_array($project->dokumen_path)
                ? ($project->dokumen_path[0] ?? null)
                : $project->dokumen_path;

            if ($dokumenPath && Storage::disk('public')->exists($dokumenPath)) {
                Storage::disk('public')->delete($dokumenPath);
            }

            $project->delete();

            // Fix norut sequence after deletion
            HistoryProyek::fixNorutSequence($idProject);

            DB::commit();

            return redirect()->route('dataproyek.show', $idProject)
                ->with('success', 'History proyek berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menghapus history proyek: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate auto ID Project with format YYYY + DD + DDDD (4 digit tahun + 2 digit tanggal + 4 digit nomor urut)
     */
    private function generateIdProject()
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y'); // 4 digit pertama: Tahun (YYYY)
        $day = $currentDate->format('d');   // 2 digit kedua: Tanggal (DD)

        // Prefix = YYYY + DD (6 digits)
        $prefix = $year . $day;

        // Get all existing project IDs with same prefix from both tables
        $existingIds = collect();

        // Check main table (data_proyek)
        $mainTableIds = DataProyek::where('id_project', 'like', $prefix . '%')
            ->pluck('id_project');
        $existingIds = $existingIds->merge($mainTableIds);

        // Check history table (history_proyek)
        $historyTableIds = HistoryProyek::where('id_project', 'like', $prefix . '%')
            ->pluck('id_project');
        $existingIds = $existingIds->merge($historyTableIds);

        // Extract 4 digit terakhir (nomor urut) dari semua ID yang ada
        $existingNumbers = $existingIds->map(function($id) {
            return (int) substr($id, 6); // Get last 4 digits as integer
        })->filter()->sort()->values(); // Remove zeros, sort ascending

        // Tentukan nomor urut berikutnya
        $nextNumber = 1; // Default: mulai dari 1

        if ($existingNumbers->isNotEmpty()) {
            // Cari gap dalam sequence atau ambil nomor tertinggi + 1
            $lastNumber = $existingNumbers->last();
            $nextNumber = $lastNumber + 1;

            // Optional: Cari gap dalam sequence (jika diperlukan)
            // for ($i = 1; $i <= $lastNumber; $i++) {
            //     if (!$existingNumbers->contains($i)) {
            //         $nextNumber = $i;
            //         break;
            //     }
            // }
        }

        // Format 4 digit terakhir dengan padding zeros
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Return full ID: YYYY + DD + NNNN
        return $prefix . $formattedNumber;
    }

    /**
     * Generate ID Project via AJAX
     */
    public function generateIdProjectAjax()
    {
        try {
            $idProject = $this->generateIdProject();
            return response()->json([
                'success' => true,
                'id_project' => $idProject
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate ID Project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dropdown data for forms
     */
    public function getDropdownData()
    {
        return [
            'konsumen' => Konsumen::where('status', 'A')->orderBy('konsumen')->get(),
            'dataPeluang' => DataPeluang::whereIn('status', ['D', 'I'])->orderBy('peluang')->get(),
            'bidangJasa' => BidangJasa::active()->orderBy('desc_bidjasa')->get(),
            'kondisiProyek' => KondisiProyek::active()->orderBy('desc_kondisi_proyek')->get(),
            'managers' => MasterManager::with('divisi')->where('status', 'A')->orderBy('nama')->get()
        ];
    }

    /**
     * Create new history for existing project (add to history)
     */
    public function createForProject(string $idProject)
    {
        // Get parent project by id_project
        $parentProject = DataProyek::where('id_project', $idProject)
            ->with(['konsumen', 'bidangJasa', 'kondisiProyek', 'manager', 'dataPeluang'])
            ->first();

        if (!$parentProject) {
            abort(404, 'Proyek tidak ditemukan.');
        }

        $data = $this->getDropdownData();
        $data['idProject'] = $idProject;
        $data['parentProject'] = $parentProject;
        $data['addToHistory'] = true;

        // Jarak options mapping
        $jarakOptions = [
            1 => 'Jarak 5KM - 10KM',
            2 => 'Jarak 21KM - 30KM',
            3 => 'Jarak 31KM - 40KM',
            4 => 'Jarak 41KM - 50KM',
            5 => 'Jarak 51KM - 60KM',
            6 => 'Jarak 11KM - 20KM'
        ];

        $data['jarakOptions'] = $jarakOptions;
        $data['jarakDisplay'] = $jarakOptions[$parentProject->jarak_lokasi] ?? 'Belum diisi';

        $data['statusOptions'] = [
            'O' => 'Open',
            'I' => 'InProgress',
            'C' => 'Close',
            'P' => 'Pending',
            'F' => 'Finish Pekerjaan'
        ];

        $data['keteranganOptions'] = [
            '1' => 'Kontrak Induk',
            '2' => 'Bukan Kontrak Induk'
        ];

        $data['defaultKeterangan'] = '2';

        return view('dataproyek.create-history', $data);
    }

    /**
     * Download document file
     */
     public function downloadDocument(Request $request, string $idProject)
    {
        // Support both old (history_id) and new (norut) parameter for backward compatibility
        $historyId = $request->query('history_id');
        $norut = $request->query('norut');

        $project = null;
        $isHistory = false;

        // Priority 1: Try composite key (norut + id_project) - NEW METHOD
        if ($norut) {
            $project = HistoryProyek::where('id_project', $idProject)
                ->where('norut', $norut)
                ->first();
            $isHistory = true;
        }

        // Priority 2: Try old history_id method (for backward compatibility)
        if (!$project && $historyId) {
            $project = HistoryProyek::where('id_project', $idProject)
                ->where('norut', $historyId)
                ->first();
            $isHistory = true;
        }

        // Priority 3: Try data_proyek (main project)
        if (!$project) {
            $project = DataProyek::where('id_project', $idProject)->first();
            $isHistory = false;
        }

        // Priority 4: Last resort - find any history by id_project
        if (!$project) {
            $project = HistoryProyek::where('id_project', $idProject)->first();
            $isHistory = true;
        }

        if (!$project) {
            abort(404, 'Proyek tidak ditemukan.');
        }

        // Note: Don't use refresh() with composite key model (HistoryProyek)
        // Data from database query is already fresh

        // FIX: Ensure dokumen_path is a string, not array
        $dokumenPath = is_array($project->dokumen_path)
            ? ($project->dokumen_path[0] ?? null)
            : $project->dokumen_path;

        if (!$dokumenPath || !is_string($dokumenPath)) {
            abort(404, 'Dokumen tidak tersedia untuk proyek ini.');
        }

        // Build full file path
        $filePath = storage_path('app/public/' . $dokumenPath);

        if (!file_exists($filePath)) {
            abort(404, 'File dokumen tidak ditemukan di storage. Path: ' . $dokumenPath);
        }

        // Get original filename
        $originalName = basename($dokumenPath);

        // Add headers to prevent caching
        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT'
        ];

        return response()->download($filePath, $originalName, $headers);
    }

    /**
     * Get project data for AJAX calls
     * FIXED: Support both old (history_id) and new (norut) parameters
     */
    public function getProjectData(Request $request, string $idProject)
    {
        // Check if we're looking for history project specifically
        $isHistorySearch = $request->query('from_history') === 'true';
        $historyId = $request->query('history_id');
        $norut = $request->query('norut');

        $project = null;
        $isHistory = false;

        // Priority 1: Search by composite key (id_project + norut) - NEW METHOD
        if ($isHistorySearch && $norut) {
            $project = HistoryProyek::with([
                'konsumen',
                'dataPeluang',
                'bidangJasa',
                'kondisiProyek',
                'manager'
            ])->where('id_project', $idProject)
              ->where('norut', $norut)
              ->first();
            $isHistory = true;
        }

        // Priority 2: Search by old history_id (for backward compatibility)
        if (!$project && $isHistorySearch && $historyId) {
            $project = HistoryProyek::with([
                'konsumen',
                'dataPeluang',
                'bidangJasa',
                'kondisiProyek',
                'manager'
            ])->where('norut', $historyId)
              ->where('id_project', $idProject)
              ->first();
            $isHistory = true;
        }

        // Priority 3: Try main data_proyek
        if (!$project) {
            $project = DataProyek::with([
                'konsumen',
                'dataPeluang',
                'bidangJasa',
                'kondisiProyek',
                'manager'
            ])->where('id_project', $idProject)->first();
        }

        // Priority 4: Last resort - any history by id_project
        if (!$project) {
            $project = HistoryProyek::with([
                'konsumen',
                'dataPeluang',
                'bidangJasa',
                'kondisiProyek',
                'manager'
            ])->where('id_project', $idProject)->first();
            $isHistory = true;
        }

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // Jarak options mapping
        $jarakOptions = [
            1 => 'Jarak 5KM - 10KM',
            2 => 'Jarak 21KM - 30KM',
            3 => 'Jarak 31KM - 40KM',
            4 => 'Jarak 41KM - 50KM',
            5 => 'Jarak 51KM - 60KM',
            6 => 'Jarak 11KM - 20KM'
        ];

        // Status options mapping
        $statusOptions = [
            'O' => 'Open',
            'I' => 'In Progress',
            'C' => 'Close',
            'P' => 'Pending',
            'F' => 'Finish Pekerjaan'
        ];

        // Keterangan options mapping
        $keteranganOptions = [
            '1' => 'Kontrak Induk',
            '2' => 'Bukan Kontrak Induk'
        ];

        // Format nilai_proyek consistently with model accessor
        $nilaiProyekFormatted = $project->nilai_proyek_formatted;

        // Keterangan formatting: history = text bebas, data_proyek = mapping 1/2
        $keteranganFormatted = $isHistory
            ? ($project->keterangan ?? '-')  // History: text bebas
            : ($keteranganOptions[$project->keterangan] ?? $project->keterangan ?? '-'); // Data Proyek: map 1/2

        return response()->json([
            'id_project' => $project->id_project,
            'norut' => $isHistory ? ($project->norut ?? '-') : null,
            'dokumen_io' => $project->dokumen_io ?? '-',
            'cost_center' => $project->cost_center,
            'namaproject' => $project->namaproject,
            'konsumen_nama' => $project->konsumen->konsumen ?? '-',
            'konsumen_alamat' => $project->konsumen->alamat ?? '-',
            'konsumen_no_hp' => $project->konsumen->no_hp ?? '-',
            'peluang_nama' => $project->dataPeluang->peluang ?? '-',
            'bidang_jasa' => $project->bidangJasa->desc_bidjasa ?? '-',
            'kondisi_proyek' => $project->kondisiProyek->desc_kondisi_proyek ?? '-',
            'lokasi_proyek' => $project->lokasi_proyek ?? '-',
            'jarak_lokasi' => $jarakOptions[$project->jarak_lokasi] ?? '-',
            'no_kontrak' => $project->no_kontrak ?? '-',
            'nilai_proyek_formatted' => $nilaiProyekFormatted,
            'nilai_proyek_raw' => $project->nilai_proyek,
            'tgl_pengakuan' => $project->tgl_pengakuan ? $project->tgl_pengakuan->format('Y-m-d') : null,
            'tgl_kontrak' => $project->tgl_kontrak ? $project->tgl_kontrak->format('Y-m-d') : null,
            'tgl_expire' => $project->tgl_expire ? $project->tgl_expire->format('Y-m-d') : null,
            'start_kontrak' => $project->start_kontrak ? $project->start_kontrak->format('Y-m-d') : null,
            'finish_kontrak' => $project->finish_kontrak ? $project->finish_kontrak->format('Y-m-d') : null,
            'penanggung_jawab' => $project->manager->nama ?? ($project->penanggung_jawab ?? '-'),
            'manager_nama' => $project->manager->nama ?? '-',
            'nik' => $project->manager->nik ?? '-',
            'status' => $project->status,
            'status_text' => $statusOptions[$project->status] ?? $project->status,
            'status_badge' => $this->getStatusBadge($project->status),
            'keterangan' => $keteranganFormatted,
            'keterangan_raw' => $project->keterangan,
            'dokumen_path' => $project->dokumen_path,
            'is_history' => $isHistory,
            'created_at' => $project->created_at ? $project->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $project->updated_at ? $project->updated_at->format('Y-m-d H:i:s') : null
        ]);
    }

    /**
     * Bulk status update
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_ids' => 'required|array',
            'project_ids.*' => 'string',
            'status' => 'required|in:O,I,C,P,F'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Update main projects
            DataProyek::whereIn('id_project', $request->project_ids)
                ->update(['status' => $request->status]);

            // Update history projects
            HistoryProyek::whereIn('id_project', $request->project_ids)
                ->update(['status' => $request->status]);

            DB::commit();

            return response()->json(['message' => 'Status berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check if cost center exists
     */
    public function checkCostCenterExists(Request $request)
    {
        $costCenter = $request->get('cost_center');

        if (!$costCenter) {
            return response()->json(['exists' => false]);
        }

        $existingProject = DataProyek::where('cost_center', $costCenter)->first();

        if ($existingProject) {
            return response()->json([
                'exists' => true,
                'project' => [
                    'id_project' => $existingProject->id_project,
                    'namaproject' => $existingProject->namaproject,
                    'add_history_url' => route('dataproyek.createForProject', $existingProject->id_project)
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Get status text
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'O' => 'Open',
            'I' => 'In Progress',
            'C' => 'Close',
            'P' => 'Pending',
            'F' => 'Finish Pekerjaan'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Get status badge
     */
    private function getStatusBadge($status)
    {
        return match($status) {
            'I' => 'badge bg-primary',
            'O' => 'badge bg-info',
            'C' => 'badge bg-success',
            'P' => 'badge bg-secondary',
            'F' => 'badge bg-warning',
            default => 'badge bg-secondary'
        };
    }

    /**
     * Get data peluang details for auto-fill
     */
    public function getDataPeluang($id)
    {
        $peluang = DataPeluang::with(['konsumen'])->find($id);

        if (!$peluang) {
            return response()->json(['error' => 'Data peluang not found'], 404);
        }

        return response()->json([
            'id_konsumen' => $peluang->id_konsumen,
            'konsumen_nama' => $peluang->konsumen->konsumen ?? '',
            'lokasi' => $peluang->lokasi,
            'biaya_peluang' => $peluang->biaya_peluang,
            'pagu_peluang' => $peluang->pagu_peluang,
            'target_peluang' => $peluang->target_peluang
        ]);
    }

    /**
     * Generate unique filename with original name
     * Jika file sudah ada, tambahkan (1), (2), dst
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory - directory dalam storage (e.g., 'dokumen_proyek')
     * @return string - path file yang disimpan
     */
    private function generateUniqueFilename($file, $directory = 'dokumen_proyek')
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);

        // Sanitize filename
        $nameWithoutExtension = preg_replace('/[^A-Za-z0-9_\-\s]/', '', $nameWithoutExtension);
        $nameWithoutExtension = str_replace(' ', '_', $nameWithoutExtension);

        $finalFileName = $nameWithoutExtension . '.' . $extension;
        $fullPath = $directory . '/' . $finalFileName;
        $counter = 1;

        // Check if file already exists
        while (Storage::disk('public')->exists($fullPath)) {
            $finalFileName = $nameWithoutExtension . '(' . $counter . ').' . $extension;
            $fullPath = $directory . '/' . $finalFileName;
            $counter++;

            // Safety limit
            if ($counter > 1000) {
                $finalFileName = $nameWithoutExtension . '_' . time() . '.' . $extension;
                $fullPath = $directory . '/' . $finalFileName;
                break;
            }
        }

        // Store file
        $file->storeAs($directory, $finalFileName, 'public');
        return $fullPath;
    }
}
