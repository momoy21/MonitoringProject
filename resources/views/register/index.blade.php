<x-layout title="Kelola Project Manager">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Kelola Project Manager</h4>
                <p class="mb-0">Daftar Project Manager dan Akses Bidang Jasa</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}" autocomplete="off">
                            <div class="loading-spinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add Button -->
                    <a href="{{ route('register.create') }}" class="btn btn-primary" onclick="if(window.StateManagers?.kelolaPM) window.StateManagers.kelolaPM.clearState();">
                        <i class="bx bx-plus me-1"></i> Tambah PM
                    </a>
                </div>
            </div>
        </div>

        <!-- Per Page & Filter Section -->
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
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 justify-content-md-end">
                    <label for="bidangJasaFilter" class="form-label mb-0">Filter Bidang Jasa:</label>
                    <select id="bidangJasaFilter" class="form-select per-page-selector">
                        <option value="">Semua Bidang Jasa</option>
                        @foreach($bidangJasas as $bidangJasa)
                            <option value="{{ $bidangJasa->id_bidjasa }}"
                                {{ request('bidang_jasa') == $bidangJasa->id_bidjasa ? 'selected' : '' }}>
                                {{ $bidangJasa->desc_bidjasa }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- <!-- Flash Messages -->
    <x-flash-messages /> --}}

    <!-- Table Section -->
    <div class="card">
        <div class="table-responsive register-table-container">
            <table class="table table-striped table-hover register-table">
                <thead>
                    <tr>
                        <th class="fw-bold">Nama</th>
                        <th class="fw-bold">Email</th>
                        <th class="fw-bold">Bidang Jasa</th>
                        <th class="fw-bold">Dibuat</th>
                        <th class="fw-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="editable-row" ondblclick="editPM({{ $user->id }})" title="Double-click untuk edit" style="cursor: pointer;">
                        <td>
                            <span class="pm-name" data-id="{{ $user->id }}">
                                <div class="truncate-text">{{ $user->name }}</div>
                            </span>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $user->email }}">
                                {{ $user->email }}
                            </div>
                        </td>
                        <td>
                            @php
                                $bidangJasaIds = $user->bidang_jasa_ids ? json_decode($user->bidang_jasa_ids, true) : [];
                                $bidangJasas = \App\Models\BidangJasa::whereIn('id_bidjasa', $bidangJasaIds)->pluck('desc_bidjasa')->toArray();
                            @endphp
                            @if(count($bidangJasas) > 0)
                                <div class="multiline-text">
                                    {{ implode(', ', $bidangJasas) }}
                                </div>
                            @else
                                <span class="text-muted">Semua Bidang Jasa</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td onclick="event.stopPropagation();">
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="viewPM({{ $user->id }})">
                                        <i class="bx bx-show me-1"></i> Lihat Detail</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="bx bx-trash me-1"></i> Hapus</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="bx bx-user-x" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2">Belum ada Project Manager terdaftar</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls - Responsive -->
    <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2" id="paginationControls">
        <!-- Left: Showing entries info -->
        <div class="pagination-info">
            <span class="text-muted medium">
                Menampilkan <span id="entriesFrom">{{ $users->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $users->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $users->total() }}</span> data
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

    <!-- View Detail Modal -->
    <div class="modal fade" id="viewPMModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Project Manager</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewPMContent">
                    <!-- Content loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus Project Manager <strong id="pmName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        class KelolaPMManager {
            constructor() {
                this.stateManager = window.StateManagers?.kelolaPM;
                this.currentPage = 1;
                this.perPage = 10;
                this.currentSearch = '';
                this.currentBidangJasa = '';
                this.searchTimeout = null;
            }

            init(config = {}) {
                console.log('[KelolaPM] Init called with config:', config);
                console.log('[KelolaPM] StateManager available:', !!this.stateManager);

                this.currentPage = config.currentPage || 1;
                this.perPage = config.perPage || 10;
                this.currentSearch = config.currentSearch || '';
                this.currentBidangJasa = config.currentBidangJasa || '';

                let shouldLoadData = false;

                // Restore state if available
                if (this.stateManager) {
                    const savedState = this.stateManager.getState();
                    console.log('[KelolaPM] Saved state:', savedState);
                    console.log('[KelolaPM] Should restore?', this.stateManager.shouldRestoreState());

                    if (savedState && this.stateManager.shouldRestoreState()) {
                        this.currentPage = savedState.currentPage || 1;
                        this.currentSearch = savedState.currentSearch || '';
                        this.perPage = savedState.perPage || 10;
                        this.currentBidangJasa = savedState.currentBidangJasa || '';

                        // Set UI inputs to restored values
                        $('#searchInput').val(this.currentSearch);
                        $('#perPageSelect').val(this.perPage);
                        $('#bidangJasaFilter').val(this.currentBidangJasa);

                        this.stateManager.clearRestoreFlag();
                        console.log('[KelolaPM] State restored:', savedState);

                        // Mark that we need to reload data with restored state
                        shouldLoadData = true;
                    }
                }

                this.initializeEventHandlers();
                this.initPagination();

                // If state was restored, load data with restored parameters
                if (shouldLoadData) {
                    console.log('[KelolaPM] Loading data with restored state');
                    this.loadPMData();
                }
            }

            initializeEventHandlers() {
                // Search handler
                $('#searchInput').on('input', (e) => {
                    const searchValue = $(e.target).val().trim();
                    this.currentSearch = searchValue;
                    this.currentPage = 1;

                    clearTimeout(this.searchTimeout);
                    $('.loading-spinner').show();

                    this.searchTimeout = setTimeout(() => {
                        this.loadPMData();
                    }, 300);
                });

                // Per page selector
                $('#perPageSelect').on('change', (e) => {
                    this.perPage = $(e.target).val();
                    this.currentPage = 1;
                    this.loadPMData();
                });

                // Bidang jasa filter
                $('#bidangJasaFilter').on('change', (e) => {
                    this.currentBidangJasa = $(e.target).val();
                    this.currentPage = 1;
                    this.loadPMData();
                });
            }

            loadPMData() {
                // Save current state
                if (this.stateManager) {
                    this.stateManager.saveState({
                        currentPage: this.currentPage,
                        currentSearch: this.currentSearch,
                        perPage: this.perPage,
                        currentBidangJasa: this.currentBidangJasa
                    });
                }

                const params = {
                    search: this.currentSearch,
                    per_page: this.perPage,
                    page: this.currentPage
                };

                if (this.currentBidangJasa) {
                    params.bidang_jasa = this.currentBidangJasa;
                }

                $.ajax({
                    url: '{{ route("register.index") }}',
                    type: 'GET',
                    data: params,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: (response) => {
                        if (response.success) {
                            $('.register-table tbody').html(response.html);
                            this.updatePaginationInfo(response.pagination);
                            this.initPagination();
                        }
                    },
                    error: (xhr) => {
                        console.error('Error loading data:', xhr);
                    },
                    complete: () => {
                        $('.loading-spinner').hide();
                    }
                });
            }

            initPagination() {
                const { current_page, last_page } = paginationData;
                this.generatePageNumbers(current_page, last_page);
                this.setupPaginationButtons(current_page, last_page);
            }

            generatePageNumbers(currentPage, lastPage) {
                const container = $('#pageNumbersContainer');
                if (!container.length) return;

                container.empty();

                let startPage, endPage;
                const maxVisible = 5;

                if (lastPage <= maxVisible) {
                    startPage = 1;
                    endPage = lastPage;
                } else {
                    if (currentPage <= 3) {
                        startPage = 1;
                        endPage = maxVisible;
                    } else if (currentPage >= lastPage - 2) {
                        startPage = lastPage - maxVisible + 1;
                        endPage = lastPage;
                    } else {
                        startPage = currentPage - 2;
                        endPage = currentPage + 2;
                    }
                }

                if (startPage > 1) {
                    this.addPageButton(container, 1, currentPage);
                    if (startPage > 2) {
                        this.addEllipsis(container);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    this.addPageButton(container, i, currentPage);
                }

                if (endPage < lastPage) {
                    if (endPage < lastPage - 1) {
                        this.addEllipsis(container);
                    }
                    this.addPageButton(container, lastPage, currentPage);
                }
            }

            addPageButton(container, pageNum, currentPage) {
                const btn = $('<button>')
                    .attr('type', 'button')
                    .addClass(`btn btn-sm ${pageNum === currentPage ? 'btn-primary' : 'btn-outline-secondary'}`)
                    .text(pageNum)
                    .on('click', () => this.navigateToPage(pageNum));

                if (pageNum === currentPage) {
                    btn.prop('disabled', true);
                }

                container.append(btn);
            }

            addEllipsis(container) {
                container.append($('<span>').addClass('px-2').text('...'));
            }

            setupPaginationButtons(currentPage, lastPage) {
                const firstBtn = $('#firstPageBtn');
                const prevBtn = $('#prevPageBtn');
                const nextBtn = $('#nextPageBtn');
                const lastBtn = $('#lastPageBtn');

                if (currentPage <= 1) {
                    firstBtn.prop('disabled', true);
                    prevBtn.prop('disabled', true);
                } else {
                    firstBtn.prop('disabled', false).off('click').on('click', () => this.navigateToPage(1));
                    prevBtn.prop('disabled', false).off('click').on('click', () => this.navigateToPage(currentPage - 1));
                }

                if (currentPage >= lastPage) {
                    nextBtn.prop('disabled', true);
                    lastBtn.prop('disabled', true);
                } else {
                    nextBtn.prop('disabled', false).off('click').on('click', () => this.navigateToPage(currentPage + 1));
                    lastBtn.prop('disabled', false).off('click').on('click', () => this.navigateToPage(lastPage));
                }
            }

            navigateToPage(page) {
                this.currentPage = page;
                this.loadPMData();
            }

            updatePaginationInfo(pagination) {
                $('#entriesFrom').text(pagination.from);
                $('#entriesTo').text(pagination.to);
                $('#entriesTotal').text(pagination.total);

                // Update global pagination data
                paginationData.current_page = pagination.current_page;
                paginationData.last_page = pagination.last_page;
                paginationData.per_page = pagination.per_page;
                paginationData.total = pagination.total;
                paginationData.from = pagination.from;
                paginationData.to = pagination.to;
            }
        }

        // Pagination Data from Laravel
        const paginationData = {
            current_page: {{ $users->currentPage() }},
            last_page: {{ $users->lastPage() }},
            per_page: {{ $users->perPage() }},
            total: {{ $users->total() }},
            from: {{ $users->firstItem() ?? 0 }},
            to: {{ $users->lastItem() ?? 0 }}
        };

        // Initialize manager
        let kelolaPMManager;
        $(document).ready(function() {
            kelolaPMManager = new KelolaPMManager();
            kelolaPMManager.init({
                currentPage: {{ $users->currentPage() }},
                perPage: {{ $users->perPage() }},
                currentSearch: '{{ request('search') }}',
                currentBidangJasa: '{{ request('bidang_jasa') }}'
            });
        });

        function editPM(id) {
            window.location.href = `/register/${id}/edit`;
        }

        function viewPM(id) {
            // Show modal
            const viewModal = new bootstrap.Modal(document.getElementById('viewPMModal'));
            viewModal.show();

            // Load data via AJAX
            fetch(`/register/${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data.user;
                        const bidangJasas = data.data.bidangJasas;

                        const createdAt = new Date(user.created_at);
                        const formattedDate = createdAt.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        const bidangJasaList = bidangJasas.length > 0
                            ? bidangJasas.map(bj => `<div>• ${bj.desc_bidjasa}</div>`).join('')
                            : '<div class="text-muted">Semua Bidang Jasa</div>';

                        const html = `
                            <div class="modal-info-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="modal-info-section">
                                            <h6>Informasi Project Manager</h6>
                                            <p><strong>Nama:</strong><br>${user.name}</p>
                                            <p><strong>Email:</strong><br>${user.email}</p>
                                            <p><strong>Tanggal Dibuat:</strong><br>${formattedDate}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="modal-info-section">
                                            <h6>Akses Bidang Jasa</h6>
                                            ${bidangJasaList}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        document.getElementById('viewPMContent').innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('viewPMContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bx bx-error me-2"></i>
                            Terjadi kesalahan saat memuat data
                        </div>
                    `;
                });
        }

        function confirmDelete(id, name) {
            // Set PM name in modal
            document.getElementById('pmName').textContent = name;

            // Set form action
            const form = document.getElementById('deleteForm');
            form.action = `/register/${id}`;

            // Mark for state restoration before form submit
            if (kelolaPMManager?.stateManager) {
                kelolaPMManager.stateManager.markForRestore();
            }

            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        // Handle bidang jasa filter change
        document.addEventListener('DOMContentLoaded', function() {
            const bidangJasaFilter = document.getElementById('bidangJasaFilter');

            if (bidangJasaFilter) {
                bidangJasaFilter.addEventListener('change', function() {
                    const url = new URL(window.location.href);

                    if (this.value) {
                        url.searchParams.set('bidang_jasa', this.value);
                    } else {
                        url.searchParams.delete('bidang_jasa');
                    }

                    // Remove page parameter when filter changes
                    url.searchParams.delete('page');

                    window.location.href = url.toString();
                });
            }
        });
    </script>
    @endpush
</x-layout>
