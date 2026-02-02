<x-layout title="Pencatatan Pleno RAB">
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Pencatatan Pleno RAB</h4>
                <p class="mb-0 text-muted">Kelola data evaluasi dan monitoring margin proyek</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <form action="{{ route('rabpleno.index') }}" method="GET">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Cari No/Proyek..." value="{{ request('search') }}">
                        </div>
                    </form>
                    <a href="{{ route('rabpleno.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah</a>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted">TAMPILKAN:</label>
                    <select class="form-select per-page-selector" style="width: auto;" onchange="window.location.href = '{{ route('rabpleno.index') }}?per_page=' + this.value">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th class="small fw-bold text-muted">NO PENGAJUAN</th>
                        <th class="small fw-bold text-muted">PROYEK</th>
                        <th class="small fw-bold text-muted text-center">MARGIN RKAP</th>
                        <th class="small fw-bold text-muted text-center">MARGIN PLENO</th>
                        <th class="small fw-bold text-muted text-center">STATUS</th>
                        <th class="small fw-bold text-muted text-end px-4">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataPleno as $item)
                    <tr ondblclick="window.location='{{ route('rabpleno.edit', $item->nopengajuan) }}'" style="cursor: pointer;">
                        <td class="fw-bold text-primary">{{ $item->nopengajuan }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->namaproject }}</div>
                            <small class="text-muted">{{ $item->cost_center }}</small>
                        </td>
                        <td class="text-center">{{ $item->marginrkap }}%</td>
                        <td class="text-center">{{ $item->marginpleno ?? 0 }}%</td>
                        <td class="text-center">
                            <span class="badge {{ $item->hasil_pleno == 'TR' ? 'bg-success' : 'bg-danger' }} px-3">
                                {{ $item->hasil_pleno == 'TR' ? 'TERCAPAI' : 'TIDAK TERCAPAI' }}
                            </span>
                        </td>
                        <td class="text-end px-4">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $item->nopengajuan }}">
                                <i class="bx bx-show"></i>
                            </button>
                        </td>
                    </tr>
                    <div class="modal fade" id="viewModal{{ $item->nopengajuan }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content border-0">
                                <div class="modal-header border-bottom-0">
                                    <h5 class="modal-title fw-bold text-primary">Detail RAB</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row mb-3">
                                        <div class="col-4 text-muted small fw-bold">NO PENGAJUAN:</div>
                                        <div class="col-8 fw-bold">{{ $item->nopengajuan }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-4 text-muted small fw-bold">NAMA PROYEK:</div>
                                        <div class="col-8 fw-bold">{{ $item->namaproject }}</div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="small text-muted mb-1">Margin RKAP</p>
                                            <h5 class="fw-bold">{{ $item->marginrkap }}%</h5>
                                        </div>
                                        <div class="col-6">
                                            <p class="small text-muted mb-1">Margin Pleno</p>
                                            <h5 class="fw-bold">{{ $item->marginpleno ?? 0 }}%</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination-controls d-flex justify-content-between mt-3 px-2">
        <div class="small text-muted">Menampilkan {{ $dataPleno->firstItem() }} - {{ $dataPleno->lastItem() }} dari {{ $dataPleno->total() }} data</div>
        {{ $dataPleno->links('pagination::bootstrap-4') }}
    </div>
</x-layout>