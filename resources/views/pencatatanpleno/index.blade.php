<x-layout title="Pencatatan Pleno RAB">
    <!-- Header Section - Sticky -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Daftar Pencatatan Pleno RAB</h4>
                <p class="mb-0">Kelola pencatatan hasil pleno rencana anggaran biaya proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <!-- Search -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="searchInput" class="form-control"
                                   placeholder="Cari proyek, konsumen, cost center..."
                                   value="{{ request('search') }}"
                                   autocomplete="off"
                                   style="min-width: 280px;">
                            <div class="loading-spinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Per Page & Info -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label for="perPageSelect" class="form-label mb-0">Tampilkan:</label>
                    <select id="perPageSelect" class="form-select per-page-selector" style="width: auto;">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ request('per_page') == 10 || !request('per_page') ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span>data per halaman</span>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    <i class="bx bx-info-circle me-1"></i>
                    Double-click pada baris untuk membuka form pencatatan pleno
                </small>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-flash-messages />

    <!-- Table Section -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th class="fw-bold text-center" style="width: 50px;">No</th>
                        <th class="fw-bold">Tanggal Pengajuan</th>
                        <th class="fw-bold">Nomor</th>
                        <th class="fw-bold">Cost Center</th>
                        <th class="fw-bold">Nama Proyek</th>
                        <th class="fw-bold">Divisi</th>
                        <th class="fw-bold">Konsumen</th>
                        <th class="fw-bold text-center">Progress</th>
                        <th class="fw-bold text-center">Hasil Pleno</th>
                        <th class="fw-bold text-center" style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pencatatanPlenoTableBody">
                    @forelse($rabProyek as $index => $item)
                    <tr class="editable-row" ondblclick="window.location.href='{{ route('pencatatanpleno.edit', $item->nopengajuan) }}'" title="Double-click untuk pencatatan pleno" style="cursor: pointer;">
                        <td class="text-center">{{ $rabProyek->firstItem() + $index }}</td>
                        <td>{{ $item->tgl_input_formatted }}</td>
                        <td>
                            <span class="fw-bold text-primary">{{ $item->nopengajuan }}</span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $item->cost_center }}</span>
                        </td>
                        <td>
                            <div class="truncate-text" title="{{ $item->nama_project }}" style="max-width: 200px;">
                                {{ Str::limit($item->nama_project, 35) }}
                            </div>
                        </td>
                        <td>{{ $item->masterDivisi->nama_divisi ?? '-' }}</td>
                        <td>{{ $item->konsumen->konsumen ?? '-' }}</td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            {!! $item->progress_badge !!}
                        </td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            {!! $item->hasil_pleno_badge !!}
                        </td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            <a href="{{ route('pencatatanpleno.show', $item->nopengajuan) }}" 
                               class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-folder-open mb-2" style="font-size: 48px; color: #a1a5b7;"></i>
                                <h5 class="mt-3 text-muted">Tidak ada data pengajuan RAB</h5>
                                <p class="text-muted">
                                    @if(request('search'))
                                        Tidak ditemukan data dengan pencarian "{{ request('search') }}"
                                    @else
                                        Belum ada data pengajuan RAB yang dapat dicatat
                                    @endif
                                </p>
                                @if(request('search') || request('progress') || request('hasil_pleno'))
                                    <a href="{{ route('pencatatanpleno.index') }}" class="btn btn-outline-primary">
                                        <i class="bx bx-refresh me-1"></i> Reset Filter
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-3 gap-2" id="paginationControls">
        <!-- Left: Showing entries info -->
        <div class="pagination-info">
            <span class="text-muted">
                Menampilkan <span id="entriesFrom">{{ $rabProyek->firstItem() ?? 0 }}</span> hingga <span id="entriesTo">{{ $rabProyek->lastItem() ?? 0 }}</span> dari <span id="entriesTotal">{{ $rabProyek->total() }}</span> data
            </span>
        </div>

        <!-- Right: Navigation buttons -->
        <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-md-end">
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="firstPageBtn" title="Halaman Pertama">
                <i class="bx bx-chevrons-left"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="prevPageBtn" title="Halaman Sebelumnya">
                <i class="bx bx-chevron-left"></i>
            </button>
            <div class="d-flex align-items-center gap-1 mx-1 mx-md-2" id="pageNumbersContainer"></div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="nextPageBtn" title="Halaman Selanjutnya">
                <i class="bx bx-chevron-right"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-block" id="lastPageBtn" title="Halaman Terakhir">
                <i class="bx bx-chevrons-right"></i>
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
    $(document).ready(function() {
        let searchTimeout;
        let currentPage = {{ $rabProyek->currentPage() }};
        let totalPages = {{ $rabProyek->lastPage() }};
        let perPage = {{ request('per_page', 10) }};
        let currentSearch = '{{ request('search') }}';

        // Search input with debounce
        $('#searchInput').on('input', function() {
            const spinner = $('.loading-spinner');
            spinner.show();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = $(this).val().trim();
                currentPage = 1;
                loadData();
            }, 300);
        });

        // Per page change
        $('#perPageSelect').on('change', function() {
            perPage = $(this).val();
            currentPage = 1;
            loadData();
        });

        // Pagination buttons
        $('#firstPageBtn').on('click', function() {
            if (currentPage > 1) {
                currentPage = 1;
                loadData();
            }
        });

        $('#prevPageBtn').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                loadData();
            }
        });

        $('#nextPageBtn').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                loadData();
            }
        });

        $('#lastPageBtn').on('click', function() {
            if (currentPage < totalPages) {
                currentPage = totalPages;
                loadData();
            }
        });

        function loadData() {
            const spinner = $('.loading-spinner');
            spinner.show();

            $.ajax({
                url: '{{ route('pencatatanpleno.index') }}',
                type: 'GET',
                data: {
                    search: currentSearch,
                    per_page: perPage,
                    page: currentPage
                },
                success: function(response) {
                    spinner.hide();
                    if (response.success) {
                        renderTable(response.data);
                        updatePagination(response.pagination);
                    }
                },
                error: function() {
                    spinner.hide();
                    console.error('Error loading data');
                }
            });
        }

        function renderTable(data) {
            const tbody = $('#pencatatanPlenoTableBody');
            tbody.empty();

            if (data.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bx bx-folder-open mb-2" style="font-size: 48px; color: #a1a5b7;"></i>
                                <h5 class="mt-3 text-muted">Tidak ada data pengajuan RAB</h5>
                                <p class="text-muted">
                                    ${currentSearch ? 'Tidak ditemukan data dengan pencarian "' + currentSearch + '"' : 'Belum ada data pengajuan RAB yang dapat dicatat'}
                                </p>
                                ${currentSearch ? '<button type="button" class="btn btn-outline-primary" onclick="$(\'\'#searchInput\'\').val(\'\'\'\'); currentSearch=\'\'\'\'; loadData();"><i class="bx bx-refresh me-1"></i> Reset Pencarian</button>' : ''}
                            </div>
                        </td>
                    </tr>
                `);
                return;
            }

            let startIndex = (currentPage - 1) * perPage + 1;
            data.forEach((item, index) => {
                const progressBadge = getProgressBadge(item.progress);
                const hasilPlenoBadge = getHasilPlenoBadge(item.hasil_pleno);
                const divisiNama = item.master_divisi ? item.master_divisi.nama_divisi : '-';
                const konsumenNama = item.konsumen ? item.konsumen.konsumen : '-';
                const namaProject = item.nama_project || '-';
                const truncatedNama = namaProject.length > 35 ? namaProject.substring(0, 35) + '...' : namaProject;
                const tglFormatted = formatDate(item.tgl_input);

                tbody.append(`
                    <tr class="editable-row" ondblclick="window.location.href='/pencatatanpleno/${item.nopengajuan}/edit'" title="Double-click untuk pencatatan pleno" style="cursor: pointer;">
                        <td class="text-center">${startIndex + index}</td>
                        <td>${tglFormatted}</td>
                        <td><span class="fw-bold text-primary">${item.nopengajuan}</span></td>
                        <td><span class="fw-bold">${item.cost_center}</span></td>
                        <td><div class="truncate-text" title="${namaProject}" style="max-width: 200px;">${truncatedNama}</div></td>
                        <td>${divisiNama}</td>
                        <td>${konsumenNama}</td>
                        <td class="text-center" onclick="event.stopPropagation();">${progressBadge}</td>
                        <td class="text-center" onclick="event.stopPropagation();">${hasilPlenoBadge}</td>
                        <td class="text-center" onclick="event.stopPropagation();">
                            <a href="/pencatatanpleno/${item.nopengajuan}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                `);
            });
        }

        function getProgressBadge(progress) {
            const badges = {
                '01': '<span class="badge bg-warning">Dokumen belum diterima</span>',
                '02': '<span class="badge bg-info">Proses TTD BOD</span>',
                '03': '<span class="badge bg-primary">Revisi RAB</span>',
                '04': '<span class="badge bg-success">Done</span>'
            };
            return badges[progress] || '<span class="badge bg-secondary">-</span>';
        }

        function getHasilPlenoBadge(hasilPleno) {
            if (!hasilPleno) return '<span class="badge bg-secondary">-</span>';
            if (hasilPleno === 'TT') return '<span class="badge bg-danger">Tidak Tercapai RKAP</span>';
            if (hasilPleno === 'TR') return '<span class="badge bg-success">Tercapai RKAP</span>';
            return '<span class="badge bg-secondary">-</span>';
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function updatePagination(pagination) {
            currentPage = pagination.current_page;
            totalPages = pagination.last_page;

            $('#entriesFrom').text(pagination.from || 0);
            $('#entriesTo').text(pagination.to || 0);
            $('#entriesTotal').text(pagination.total);

            // Update button states
            $('#firstPageBtn, #prevPageBtn').prop('disabled', currentPage <= 1);
            $('#nextPageBtn, #lastPageBtn').prop('disabled', currentPage >= totalPages);

            // Generate page numbers
            generatePageNumbers();
        }

        function generatePageNumbers() {
            const container = $('#pageNumbersContainer');
            container.empty();

            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const isActive = i === currentPage;
                container.append(`
                    <button type="button" class="btn btn-sm ${isActive ? 'btn-primary' : 'btn-outline-secondary'}" 
                            onclick="goToPage(${i})" ${isActive ? 'disabled' : ''}>
                        ${i}
                    </button>
                `);
            }
        }

        window.goToPage = function(page) {
            currentPage = page;
            loadData();
        };

        // Initialize pagination buttons
        generatePageNumbers();
        $('#firstPageBtn, #prevPageBtn').prop('disabled', currentPage <= 1);
        $('#nextPageBtn, #lastPageBtn').prop('disabled', currentPage >= totalPages);
    });
    </script>
    @endpush
</x-layout>
