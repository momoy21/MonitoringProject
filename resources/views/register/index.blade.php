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
                    <!-- Add Button -->
                    <a href="{{ route('register.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah PM
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="row mt-3 align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
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

        @if($users->hasPages())
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
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
