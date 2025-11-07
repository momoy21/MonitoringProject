<x-layout title="Detail Project Manager">
    <!-- Header Section - Non Sticky -->
    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Detail Project Manager</h4>
                <p class="mb-0">Informasi lengkap Project Manager</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('register.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
                <a href="{{ route('register.edit', $user->id) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Detail Content -->
    <div class="card">
        <div class="card-body">
            <!-- Informasi Umum -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="bx bx-user me-2"></i>Informasi Umum
                    </h5>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="info-item-card">
                        <label class="text-muted mb-1">Nama Lengkap</label>
                        <div class="fw-semibold">{{ $user->name }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item-card">
                        <label class="text-muted mb-1">Email</label>
                        <div class="fw-semibold">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item-card">
                        <label class="text-muted mb-1">Role</label>
                        <div>
                            <span class="badge bg-primary">Project Manager</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item-card">
                        <label class="text-muted mb-1">Tanggal Dibuat</label>
                        <div class="fw-semibold">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Akses Bidang Jasa -->
            <div class="row mb-3">
                <div class="col-12">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">
                        <i class="bx bx-briefcase me-2"></i>Akses Bidang Jasa
                    </h5>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="info-item-card">
                        @if($bidangJasas->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="100px" class="text-center">No</th>
                                            <th>Bidang Jasa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bidangJasas as $index => $bj)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $bj->desc_bidjasa }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Project Manager ini memiliki akses ke {{ $bidangJasas->count() }} bidang jasa
                                </small>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-info-circle me-2"></i>
                                Project Manager ini memiliki akses ke <strong>semua bidang jasa</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
