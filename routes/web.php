<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\BidangJasaController;
use App\Http\Controllers\DataPeluangController;
use App\Http\Controllers\DataProyekController;
use App\Http\Controllers\KondisiProyekController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\MasterManagerController;
use App\Http\Controllers\RABController;
use App\Http\Controllers\SpesifikasiRABController;
use App\Http\Controllers\SummaryRABController;
use App\Http\Controllers\ProgressProyekController;
use App\Http\Controllers\BeritaAcaraProjectController;
use App\Http\Controllers\IssueProyekController;
use App\Http\Controllers\PendapatanProyekController;
use App\Http\Controllers\SAPImportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\ReportProgramController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// ===================================================================
// AUTHENTICATED & VERIFIED ROUTES
// ===================================================================
Route::middleware(['auth', 'verified'])->group(function () {

// ===================================================================
// laporan-progress-proyek
// ===================================================================    

Route::get('/laporan-progress-proyek', [ReportProgramController::class, 'index'])->name('report.index');

Route::get('/laporan-progress-proyek/pdf', [ReportProgramController::class, 'exportPdf'])->name('report.pdf');
Route::get('/laporan-progress-proyek/excel', [ReportProgramController::class, 'exportExcel'])->name('report.excel');


    
    
    // ===================================================================
    // DASHBOARD - Redirect based on role
    // ===================================================================
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('Super Admin')) {
            return redirect()->route('konsumen.index');
        } elseif ($user->hasRole('Project Manager')) {
            return redirect()->route('dataproyek.index');
        }

        return redirect()->route('konsumen.index');
    })->name('dashboard');

    // ===================================================================
    // PROFILE ROUTES
    // ===================================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::put('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // ===================================================================
    // SUPER ADMIN ONLY ROUTES
    // ===================================================================
    Route::group(['middleware' => function ($request, $next) {
        if (!$request->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }], function () {

        // ---------------------------------------------------------------
        // REGISTER PROJECT MANAGER
        // ---------------------------------------------------------------
        Route::resource('register', RegisterController::class);

        // ---------------------------------------------------------------
        // BIDANG JASA
        // ---------------------------------------------------------------
        Route::resource('bidangjasa', BidangJasaController::class)->parameters([
            'bidangjasa' => 'bidangjasa:id_bidjasa'
        ]);

        // ---------------------------------------------------------------
        // MASTER MANAGER
        // ---------------------------------------------------------------
        Route::resource('mastermanager', MasterManagerController::class)->parameters([
            'mastermanager' => 'mastermanager:nik'
        ]);
        // ---------------------------------------------------------------
        // KONDISI PROYEK
        // ---------------------------------------------------------------
        Route::resource('kondisiproyek', KondisiProyekController::class)->parameters([
            'kondisiproyek' => 'kondisiproyek:id_kondisi_proyek'
        ]);

        // ---------------------------------------------------------------
        // SPESIFIKASI RAB
        // ---------------------------------------------------------------
        Route::resource('spesifikasirab', SpesifikasiRABController::class)->parameters([
            'spesifikasirab' => 'spesifikasirab:id_spec'
        ]);

        // ---------------------------------------------------------------
        // SUMMARY RAB - FIXED: Gunakan idsummary yang benar
        // ---------------------------------------------------------------
        Route::resource('summaryrab', SummaryRABController::class)->parameters([
            'summaryrab' => 'summaryrab:idsummary'
        ]);
        // Summary RAB - Additional API Routes
        Route::prefix('api/summaryrab')->name('api.summaryrab.')->group(function () {
            Route::get('active', [SummaryRABController::class, 'getActiveSummaryRAB'])->name('active');
        });
    });

    // ===================================================================
    // SHARED ROUTES (Super Admin & Project Manager)
    // ===================================================================
    Route::group(['middleware' => function ($request, $next) {
        if (!$request->user()->hasAnyRole(['Super Admin', 'Project Manager'])) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }], function () {

        // ---------------------------------------------------------------
        // KONSUMEN
        // ---------------------------------------------------------------
        Route::prefix('konsumen')->name('konsumen.')->group(function () {
            Route::get('/', [KonsumenController::class, 'index'])->name('index');
            Route::get('/create', [KonsumenController::class, 'create'])->name('create');
            Route::post('/', [KonsumenController::class, 'store'])->name('store');
            Route::get('/{konsumen:id_konsumen}', [KonsumenController::class, 'show'])->name('show');
            Route::get('/{konsumen:id_konsumen}/edit', [KonsumenController::class, 'edit'])->name('edit');
            Route::put('/{konsumen:id_konsumen}', [KonsumenController::class, 'update'])->name('update');
            Route::patch('/{konsumen:id_konsumen}', [KonsumenController::class, 'update']);
            Route::delete('/{konsumen:id_konsumen}', [KonsumenController::class, 'destroy'])->name('destroy');
        });

        // Konsumen - AJAX API Routes
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('cities', [KonsumenController::class, 'getCities'])->name('cities');
            Route::get('all-cities', [KonsumenController::class, 'getAllCities'])->name('all-cities');
        });

        // ---------------------------------------------------------------
        // DATA PELUANG
        // ---------------------------------------------------------------
        Route::resource('datapeluang', DataPeluangController::class)->parameters([
            'datapeluang' => 'datapeluang:id_datapeluang'
        ]);

        // ---------------------------------------------------------------
        // DATA PROYEK
        // ---------------------------------------------------------------
        // DATA PROYEK - Updated Routes with Composite Key Support
        Route::prefix('dataproyek')->name('dataproyek.')->group(function () {
            // Main CRUD routes
            Route::get('/', [DataProyekController::class, 'index'])->name('index');
            Route::get('/create', [DataProyekController::class, 'create'])->name('create');
            Route::post('/', [DataProyekController::class, 'store'])->name('store');

            // Specific routes (must be before generic routes to avoid conflicts)
            Route::get('/{idProject}/add-history', [DataProyekController::class, 'createForProject'])->name('createForProject');
            Route::post('/{idProject}/store-history', [DataProyekController::class, 'storeHistory'])->name('storeHistory');

            // Update routes untuk history
            Route::get('/history/{idProject}/{norut}/edit', [DataProyekController::class, 'editHistory'])->name('editHistory');
            Route::put('/history/{idProject}/{norut}', [DataProyekController::class, 'updateHistory'])->name('updateHistory');
            Route::delete('/history/{idProject}/{norut}', [DataProyekController::class, 'destroyHistory'])->name('destroyHistory');
            Route::get('/download/{idProject}', [DataProyekController::class, 'downloadDocument'])->name('download');
            Route::get('/get-data/{idProject}', [DataProyekController::class, 'getProjectData'])->name('getData');
            Route::get('/get-peluang/{id}', [DataProyekController::class, 'getDataPeluang'])->name('getPeluang');

            // Generic CRUD routes
            Route::get('/{idProject}/edit', [DataProyekController::class, 'edit'])->name('edit');
            Route::put('/{idProject}', [DataProyekController::class, 'update'])->name('update');
            Route::patch('/{idProject}', [DataProyekController::class, 'update']);
            Route::delete('/{idProject}', [DataProyekController::class, 'destroy'])->name('destroy');

            // Show specific project by id_project (must be last)
            Route::get('/{idProject}', [DataProyekController::class, 'show'])->name('show');

            // AJAX routes
            Route::post('/check-cost-center', [DataProyekController::class, 'checkCostCenterExists'])->name('checkCostCenter');
            Route::post('/bulk-update-status', [DataProyekController::class, 'bulkUpdateStatus'])->name('bulkUpdateStatus');
            Route::post('/generate-id', [DataProyekController::class, 'generateIdProjectAjax'])->name('generateId');
        });

        // ---------------------------------------------------------------
        // RAB (Rencana Anggaran Biaya)
        // ---------------------------------------------------------------
        Route::prefix('rab')->name('rab.')->group(function () {
            // Main Upload Page
            Route::get('/upload', [RABController::class, 'upload'])->name('upload');
            Route::post('/upload', [RABController::class, 'store'])->name('store');

            // Upload Excel RAB
            Route::post('/upload-excel', [RABController::class, 'uploadRABExcel'])->name('uploadExcel');

            // AJAX / Helper Routes
            Route::get('/get-cost-center-proyek', [RABController::class, 'getCostCenterProyek'])->name('getCostCenterProyek');
            Route::post('/check-header', [RABController::class, 'checkHeaderRAB'])->name('checkHeaderRAB');
            Route::get('/generate-id', [RABController::class, 'generateIdRABAjax'])->name('generateIdRAB');
            Route::post('/header', [RABController::class, 'storeHeaderRAB'])->name('storeHeaderRAB');
            Route::put('/header', [RABController::class, 'updateHeaderRAB'])->name('updateHeaderRAB');
            Route::get('/detail', [RABController::class, 'getDetailRAB'])->name('getDetailRAB');
            Route::get('/summary-detail', [RABController::class, 'getSummaryDetailRAB'])->name('getSummaryDetailRAB');
        });

        Route::prefix('progressproyek')->name('progressproyek.')->group(function () {
            Route::get('get-header-rab', [ProgressProyekController::class, 'getHeaderRAB'])->name('getheaderrab');
            Route::post('check-header-progress', [ProgressProyekController::class, 'checkHeaderProgress'])->name('checkheaderprogress');
        });

        // ---------------------------------------------------------------
        // Progress Project - Resource Routes
        // ---------------------------------------------------------------
        Route::resource('progressproyek', ProgressProyekController::class)->parameters([
            'progressproyek' => 'id'
        ])->except(['index']);

        Route::get('progressproyek', [ProgressProyekController::class, 'index'])->name('progressproyek.index');

        // ---------------------------------------------------------------
        // Berita Acara Project - AJAX Routes
        // ---------------------------------------------------------------
        Route::prefix('berita-acara')->name('beritaacara.')->group(function () {
            Route::get('/get-by-project', [BeritaAcaraProjectController::class, 'getBeritaAcaraByProject'])->name('getbyproject');
            Route::post('/store', [BeritaAcaraProjectController::class, 'store'])->name('store');
            Route::put('/update/{noBA}', [BeritaAcaraProjectController::class, 'update'])->name('update');
            Route::post('/update-status', [BeritaAcaraProjectController::class, 'updateStatus'])->name('updatestatus');
            Route::delete('/destroy/{noBA}', [BeritaAcaraProjectController::class, 'destroy'])->name('destroy');
        });

        // ---------------------------------------------------------------
        // Issue Project - AJAX Routes
        // ---------------------------------------------------------------
        Route::prefix('issue')->name('issue.')->group(function () {
            Route::get('/get-by-project', [IssueProyekController::class, 'getIssueByProject'])->name('getbyproject');
            Route::post('/store', [IssueProyekController::class, 'store'])->name('store');
            Route::put('/update/{noIssue}', [IssueProyekController::class, 'update'])->name('update');
            Route::post('/update-status', [IssueProyekController::class, 'updateStatus'])->name('updatestatus');
            Route::delete('/destroy/{noIssue}', [IssueProyekController::class, 'destroy'])->name('destroy');
        });

        // ---------------------------------------------------------------
        // Pendapatan Proyek - CRUD & AJAX Routes
        // ---------------------------------------------------------------
        Route::prefix('pendapatan')->name('pendapatan.')->group(function () {
            // Main index page
            Route::get('/', [PendapatanProyekController::class, 'index'])->name('index');

            // Get approved BA for dropdown
            Route::get('/approved-ba', [PendapatanProyekController::class, 'getApprovedBeritaAcara'])->name('getApprovedBA');

            // Get pendapatan by header progress (all BA)
            Route::get('/by-ba', [PendapatanProyekController::class, 'getPendapatanByBA'])->name('getByBA');

            // Get approved BA list by header progress
            Route::get('/approved-ba-by-header', [PendapatanProyekController::class, 'getApprovedBAByHeader'])->name('getApprovedBAByHeader');

            // CRUD operations
            Route::post('/store', [PendapatanProyekController::class, 'store'])->name('store');
            Route::put('/{noPendapatan}', [PendapatanProyekController::class, 'update'])->name('update');
            Route::delete('/{noPendapatan}', [PendapatanProyekController::class, 'destroy'])->name('destroy');

            // Download file
            Route::get('/{noPendapatan}/download', [PendapatanProyekController::class, 'download'])->name('download');
        });
    });
});

// SAP Import Routes
Route::middleware(['auth'])->prefix('sap')->name('sap.')->group(function () {
    Route::get('/', [SAPImportController::class, 'index'])->name('index');
    Route::delete('/truncate', [SAPImportController::class, 'truncate'])->name('truncate');
    Route::delete('/delete-by-source', [SAPImportController::class, 'deleteBySource'])->name('deleteBySource');
    Route::get('/source-files', [SAPImportController::class, 'getSourceFiles'])->name('sourceFiles');
    Route::get('/import-history', [SAPImportController::class, 'getImportHistory'])->name('importHistory');
    Route::get('/error-logs', [SAPImportController::class, 'getErrorLogs'])->name('errorLogs');
    Route::get('/import-logs', [SAPImportController::class, 'getImportLogs'])->name('importLogs');
    Route::get('/auto-import-logs', [SAPImportController::class, 'getAutoImportLogs'])->name('autoImportLogs');

    // FTP Routes
    Route::get('/ftp/test', [SAPImportController::class, 'testFtpConnection'])->name('ftp.test');
    Route::get('/ftp/files', [SAPImportController::class, 'listFtpFiles'])->name('ftp.files');
    Route::get('/ftp/directories', [SAPImportController::class, 'getFtpDirectories'])->name('ftp.directories');
    Route::get('/ftp/info', [SAPImportController::class, 'getFtpInfo'])->name('ftp.info');
    Route::post('/ftp/import', [SAPImportController::class, 'importFromFtp'])->name('ftp.import');
});

require __DIR__.'/auth.php';
