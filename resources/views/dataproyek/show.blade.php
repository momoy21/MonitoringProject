<x-layout title="Detail Data Proyek">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [
            ['name' => 'Data Proyek', 'url' => route('dataproyek.index')],
            ['name' => 'Detail Project: ' . $mainProject->id_project]
        ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <!-- Header Section - Sticky -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">{{ $mainProject->id_project }} - {{ $mainProject->namaproject }}</h4>
                <p class="mb-0">History proyek dengan ID Project yang sama (Cost Center: {{ $mainProject->cost_center }})</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="historySearchInput" class="form-control"
                                   placeholder="Cari no kontrak, nama proyek..."
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                            <div class="loading-spinner">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add to History Button -->
                    @if($mainProject->status === 'C')
                        <button type="button" class="btn btn-secondary" disabled title="Status sudah close, tidak dapat ditambah">
                            <i class="bx bx-plus me-1"></i> Tambah
                        </button>
                    @else
                        <a href="{{ route('dataproyek.createForProject', $idProject) }}" class="btn btn-primary" title="Tambah proyek ke history">
                            <i class="bx bx-plus me-1"></i> Tambah
                        </a>
                    @endif
                    <!-- Back Button -->
                    <a href="{{ route('dataproyek.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Per Page & Info -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label for="perPageSelect" class="form-label mb-0">Tampilkan:</label>
                    <select id="perPageSelect" class="form-select per-page-selector">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data per halaman</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-flash-messages />

    {{-- <!-- Main Project Info -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <div class="d-flex align-items-center">
                <i class="bx bx-bookmark text-primary me-2"></i>
                <h6 class="mb-0">Informasi Proyek Utama</h6>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mt-2">
                <!-- Baris pertama -->
                <div class="col-md-4">
                    <div class="info-item-card">
                        <label class="text-muted small mb-1">ID Project</label>
                        <div class="proyek-id-main-container">
                            <span class="fw-semibold text-primary proyek-id proyek-id-main" data-id="{{ $mainProject->id_project }}" ondblclick="editDataProyek('{{ $mainProject->id_project }}')" title="Double-click untuk edit">{{ $mainProject->id_project }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item-card">
                        <label class="text-muted small mb-1">Nama Proyek</label>
                        <div class="fw-medium" title="{{ $mainProject->namaproject }}">
                            {{ $mainProject->namaproject }}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item-card">
                        <label class="text-muted small mb-1">Nomor Kontrak/PO/JO/SPK</label>
                        <div class="fw-medium" title="{{ $mainProject->no_kontrak ?? 'Belum ada' }}">
                            {{ $mainProject->no_kontrak ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <!-- Baris kedua -->
                <div class="col-md-4">
                    <div class="info-item-card">
                        <label class="text-muted small mb-1">Konsumen</label>
                        <div class="fw-medium">{{ $mainProject->konsumen->konsumen ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item-card">
                        <label class="text-muted small mb-1">Nilai Proyek</label>
                        <div class="fw-semibold">
                            @if($mainProject->nilai_proyek)
                                <span class="text-success">
                                    <small class="currency-display">{!! $mainProject->nilai_proyek_formatted !!}</small>
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-item-card">
                        <label class="text-muted small mb-1">Periode Kontrak</label>
                        <div class="fw-medium">
                            @if($mainProject->start_kontrak && $mainProject->finish_kontrak)
                                <small>{{ $mainProject->start_kontrak->format('d/m/Y') }} - {{ $mainProject->finish_kontrak->format('d/m/Y') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <!-- History Projects Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bx bx-history me-2"></i>History Proyek ({{ $historyProjects->total() }} proyek)</h6>
            @if(request('search'))
                <span class="badge bg-info">Pencarian: "{{ request('search') }}"</span>
            @endif
        </div>
        <div class="card-body p-0">
            @if($historyProjects->count() > 0)
                <div class="table-responsive dataproyek-table-container">
                    <table class="table table-striped table-hover dataproyek-table" id="historyProyekTable">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-bold text-center" width="50">No.</th>
                                <th class="fw-bold">Nama Proyek</th>
                                <th class="fw-bold">No Kontrak<br>
                                <small class="text-muted fw-normal">No PO/No JO/No SPK</small></th>
                                <th class="fw-bold">Nilai Proyek</th>
                                <th class="fw-bold">Tanggal Kontrak</th>
                                <th class="fw-bold">Status</th>
                                <th class="fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historyProjects as $index => $project)
                                <tr class="editable-row">
                                    <td class="text-center" ondblclick="editHistoryProyek('{{ $project->id_project }}', {{ $project->norut }})" style="cursor: pointer;" title="Double-click untuk edit">
                                        <span class="norut-edit">{{ $project->norut }}</span>
                                    </td>
                                    <td ondblclick="editHistoryProyek('{{ $project->id_project }}', {{ $project->norut }})" style="cursor: pointer;" title="Double-click untuk edit">
                                        <div class="truncate-text" style="max-width: 250px;" title="{{ $project->namaproject }}">
                                            {{ $project->namaproject }}
                                        </div>
                                    </td>
                                    <td ondblclick="editHistoryProyek('{{ $project->id_project }}', {{ $project->norut }})" style="cursor: pointer;" title="Double-click untuk edit">{{ $project->no_kontrak ?: '-' }}</td>
                                    <td class="text-start" ondblclick="editHistoryProyek('{{ $project->id_project }}', {{ $project->norut }})" style="cursor: pointer;" title="Double-click untuk edit">
                                        @if($project->nilai_proyek)
                                            <small class="currency-display">{!! $project->nilai_proyek_formatted !!}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td ondblclick="editHistoryProyek('{{ $project->id_project }}', {{ $project->norut }})" style="cursor: pointer;" title="Double-click untuk edit">
                                        <div class="small">
                                            @if($project->tgl_kontrak)
                                                <div><strong>Kontrak:</strong> {{ \Carbon\Carbon::parse($project->tgl_kontrak)->format('d/m/Y') }}</div>
                                            @endif
                                            @if($project->start_kontrak && $project->finish_kontrak)
                                                <div><strong>Periode:</strong> {{ \Carbon\Carbon::parse($project->start_kontrak)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($project->finish_kontrak)->format('d/m/Y') }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="{{ $project->status_badge }}">
                                            {{ $project->status_text }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><button type="button" class="dropdown-item btn-detail"
                                                    data-id="{{ $project->id_project }}"
                                                    data-norut="{{ $project->norut }}"
                                                    data-from-history="true">
                                                    <i class="bx bx-info-circle me-1"></i> Lihat Detail</button></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><button type="button" class="dropdown-item text-danger btn-delete-history"
                                                    data-id="{{ $project->id_project }}"
                                                    data-norut="{{ $project->norut }}"
                                                    data-nama="{{ $project->namaproject }}">
                                                    <i class="bx bx-trash me-1"></i> Hapus</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls - Responsive -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2 p-3 border-top" id="paginationControls">
                    <!-- Left: Showing entries info -->
                    <div class="pagination-info">
                        <span class="text-muted medium">
                            Menampilkan <span id="entriesFrom">{{ $historyProjects->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $historyProjects->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $historyProjects->total() }}</span> data
                        </span>
                    </div>

                    <!-- Right: Navigation buttons -->
                    <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-md-end">
                        <!-- First Page - Hidden on small screens -->
                        <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="firstPageBtn" title="Halaman Pertama">
                            <i class="bx bx-chevrons-left"></i>
                        </button>

                        <!-- Previous Page -->
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="prevPageBtn" title="Halaman Sebelumnya">
                            <i class="bx bx-chevron-left"></i>
                        </button>

                        <!-- Page Numbers Container -->
                        <div class="d-flex align-items-center gap-1 mx-1 mx-md-2" id="pageNumbersContainer">
                            <!-- Page numbers will be generated here -->
                        </div>

                        <!-- Next Page -->
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="nextPageBtn" title="Halaman Selanjutnya">
                            <i class="bx bx-chevron-right"></i>
                        </button>

                        <!-- Last Page - Hidden on small screens -->
                        <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="lastPageBtn" title="Halaman Terakhir">
                            <i class="bx bx-chevrons-right"></i>
                        </button>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-history mb-2 empty-state-icon" style="font-size: 48px;"></i>
                        <h5 class="mt-3 empty-state-text">Belum ada history proyek</h5>
                        <p class="text-muted">
                            @if(request('search'))
                                Tidak ditemukan history dengan pencarian "{{ request('search') }}"
                            @else
                                Belum ada proyek yang ditambahkan ke history cost center ini
                            @endif
                        </p>
                        @if(request('search'))
                            <a href="{{ route('dataproyek.show', $idProject) }}" class="btn btn-outline-primary">
                                <i class="bx bx-refresh me-1"></i> Reset Pencarian
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Card -->
    @if($historyProjects->count() > 0)
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total History Proyek</h5>
                        <h3 class="text-primary">{{ $historyProjects->total() }}</h3>
                        <small class="text-muted">Jumlah history proyek</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Nilai History</h5>
                        @php
                            $totalNilai = $historyProjects->sum('nilai_proyek');
                        @endphp
                        <h3 class="text-success">
                            @if($totalNilai > 0)
                                Rp {{ number_format($totalNilai, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </h3>
                        <small class="text-muted">Total nilai history proyek</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">Status Aktif (History)</h5>
                        @php
                            $activeCount = $historyProjects->where('status', 'I')->count();
                        @endphp
                        <h3 class="text-warning">{{ $activeCount }}</h3>
                        <small class="text-muted">History proyek sedang berjalan</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modals -->
    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Data Proyek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailModalContent">
                    {{-- <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;" id="detailLoadingSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div> --}}
                    <div id="detailContent" style="display: none;">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus proyek <strong id="deleteProjectName"></strong> dari history?</p>
                    <input type="hidden" id="deleteProjectId">
                    <input type="hidden" id="deleteProjectNorut">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/file-preview.js') }}"></script>
        <script src="{{ asset('js/dataproyek.js') }}"></script>
        <script>
        // Function untuk edit data proyek utama (double click pada ID)
        function editDataProyek(idProject) {
            window.location.href = '/dataproyek/' + idProject + '/edit';
        }

        // Function untuk edit history proyek (double click pada nomor urut)
        // FIXED: Use composite key (idProject + norut) instead of auto-increment id
        function editHistoryProyek(idProject, norut) {
            // Use composite key for history proyek edit URL
            const baseUrl = "{{ url('dataproyek/history') }}";
            const editUrl = baseUrl + '/' + idProject + '/' + norut + '/edit';
            console.log('Edit History Debug:', {
                idProject: idProject,
                norut: norut,
                baseUrl: baseUrl,
                editUrl: editUrl
            });
            window.location.href = editUrl;
        }

        $(document).ready(function() {
            // Initialize DataProyekManager for show page with AJAX support
            window.dataProyekManager = new DataProyekManager();
            window.dataProyekManager.init({
                pageType: 'show',
                idProject: '{{ $idProject }}',
                currentPage: {{ $historyProjects->currentPage() }},
                totalPages: {{ $historyProjects->lastPage() }},
                perPage: {{ request('per_page', 15) }},
                currentSearch: '{{ request('search') }}'
            });

            // Auto-focus search input if there's a search term (restore cursor position)
            @if(request('search'))
                const searchInput = $('#historySearchInput');
                if (searchInput.length) {
                    searchInput.focus();
                    // Move cursor to end of text
                    const inputValue = searchInput.val();
                    searchInput.val('').val(inputValue);
                }
            @endif
        });
        </script>
    @endpush
</x-layout>
