<x-layout title="Data Proyek">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Data Proyek</h4>
                <p class="mb-0">Kelola semua data proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control"
                                   placeholder="Cari proyek atau konsumen..."
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                            <div class="loading-spinner">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add Button -->
                    <a href="{{ route('dataproyek.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah
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

    <!-- Table Section -->
    <div class="card">
        <div class="card-body p-0">
            @if($projects->count() > 0)
                <div class="table-responsive dataproyek-table-container">
                    <table class="table table-striped table-hover dataproyek-table" id="dataProyekTable">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-bold">Cost Center</th>
                                <th class="fw-bold">Nama Proyek</th>
                                <th class="fw-bold">Konsumen</th>
                                <th class="fw-bold">No Kontrak<br>
                                <small class="text-muted fw-normal">No PO/No JO/No SPK</small></th>
                                <th class="fw-bold">Nilai Proyek</th>
                                <th class="fw-bold">Tanggal Kontrak</th>
                                <th class="fw-bold">Status</th>
                                <th class="fw-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                                                        @foreach($projects as $project)
                                <tr class="editable-row">
                                    <td ondblclick="editDataProyek('{{ $project->id_project }}')" style="cursor: pointer;" title="Double-click untuk edit">
                                        <div class="costcenter-id">
                                            {{ $project->cost_center }}
                                        </div>
                                        <a href="{{ route('dataproyek.show', $project->id_project) }}" class="small" style="color: grey; margin-left: 15px;" onclick="event.stopPropagation();">
                                            Detail
                                        </a>
                                    </td>
                                    <td ondblclick="editDataProyek('{{ $project->id_project }}')" style="cursor: pointer;" title="Double-click untuk edit">
                                        <div class="truncate-text" title="{{ $project->namaproject }}">
                                            {{ $project->namaproject }}
                                        </div>
                                    </td>
                                    <td ondblclick="editDataProyek('{{ $project->id_project }}')" style="cursor: pointer;" title="Double-click untuk edit">
                                        <div class="truncate-text" title="{{ $project->konsumen->konsumen ?? '-' }}">
                                            {{ $project->konsumen->konsumen ?? '-' }}
                                        </div>
                                    </td>
                                    <td ondblclick="editDataProyek('{{ $project->id_project }}')" style="cursor: pointer;" title="Double-click untuk edit">{{ $project->no_kontrak ?: '-' }}</td>
                                    <td class="text-start" ondblclick="editDataProyek('{{ $project->id_project }}')" style="cursor: pointer;" title="Double-click untuk edit">
                                        @if($project->nilai_proyek)
                                            <small class="currency-display">{!! $project->nilai_proyek_formatted !!}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td ondblclick="editDataProyek('{{ $project->id_project }}')" style="cursor: pointer;" title="Double-click untuk edit">
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
                                        <span class="{{ $project->status_badge }}">{{ $project->status_text }}</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><button type="button" class="dropdown-item btn-detail" data-id="{{ $project->id_project }}">
                                                    <i class="bx bx-info-circle me-1"></i> Lihat Detail</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls - Responsive -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2" id="paginationControls">
                    <!-- Left: Showing entries info -->
                    <div class="pagination-info">
                        <span class="text-muted medium">
                            Menampilkan <span id="entriesFrom">{{ $projects->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $projects->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $projects->total() }}</span> data
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
                        <i class="bx bx-folder-open mb-2 empty-state-icon" style="font-size: 48px;"></i>
                        <h5 class="mt-3 empty-state-text">Tidak ada data proyek</h5>
                        <p class="text-muted">
                            @if(request('search'))
                                Tidak ditemukan data dengan pencarian "{{ request('search') }}"
                            @else
                                Belum ada data proyek yang ditambahkan
                            @endif
                        </p>
                        @if(request('search'))
                            <a href="{{ route('dataproyek.index') }}" class="btn btn-outline-primary">
                                <i class="bx bx-refresh me-1"></i> Reset Pencarian
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

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

    @push('scripts')
        <script src="{{ asset('js/file-preview.js') }}"></script>
        <script src="{{ asset('js/dataproyek.js') }}"></script>
        <script>
        $(document).ready(function() {
            // Auto-focus search input if there's a search term (restore cursor position)
            @if(request('search'))
                const searchInput = $('#searchInput');
                if (searchInput.length) {
                    searchInput.focus();
                    // Move cursor to end of text
                    const inputValue = searchInput.val();
                    searchInput.val('').val(inputValue);
                }
            @endif

            // Initialize data proyek manager dengan konfigurasi untuk halaman index
            window.dataProyekManager = new DataProyekManager();

            window.dataProyekManager.init({
                pageType: 'index',
                currentPage: {{ $projects->currentPage() }},
                totalPages: {{ $projects->lastPage() }},
                perPage: {{ request('per_page', 10) }},
                currentSearch: '{{ request('search') }}'
            });
        });

        // Function untuk edit data proyek (double click)
        function editDataProyek(idProject) {
            window.location.href = '/dataproyek/' + idProject + '/edit';
        }
        </script>
    @endpush
</x-layout>
