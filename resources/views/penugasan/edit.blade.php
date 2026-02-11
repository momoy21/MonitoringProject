<x-layout title="Edit Penugasan">
    <x-slot name="breadcrumbs">
        @php
            $breadcrumbs = [
                ['name' => 'Pengajuan Penugasan', 'url' => route('penugasan.index')],
                ['name' => 'Edit Penugasan']
            ];
        @endphp
    </x-slot>

    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    {{-- HEADER --}}
    <div class="nonsticky-header mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-0">
                    Edit Penugasan : {{ $penugasan->IDPenugasan }}
                </h4>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('penugasan.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('penugasan.update', $penugasan->IDPenugasan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- ================= KIRI (OTOMATIS) ================= --}}
                    <div class="col-md-6 border-end">
                        <h6 class="text-primary fw-bold mb-3">Informasi Otomatis</h6>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">ID Penugasan</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $penugasan->IDPenugasan }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">No Surat</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $penugasan->NoSurat }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Cost Center</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $penugasan->cost_center }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nama Proyek</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $penugasan->proyek->NamaProyek ?? '-' }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">NIK / Nama Karyawan</label>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $penugasan->NIK }} - {{ $penugasan->karyawan->Nama ?? '-' }}"
                                   readonly>
                        </div>
                    </div>

                    {{-- ================= KANAN (EDITABLE) ================= --}}
                    <div class="col-md-6 ps-md-4">
                        <h6 class="text-primary fw-bold mb-3">Detail Penugasan</h6>

                        {{-- TANGGAL INPUT (DI ATAS JABATAN) --}}
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Tanggal Input</label>
                            <input type="date" class="form-control bg-light"
                                   value="{{ \Carbon\Carbon::parse($penugasan->TanggalInput)->format('Y-m-d') }}"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Jabatan</label>
                            <input type="text" name="Jabatan" class="form-control"
                                   value="{{ old('Jabatan', $penugasan->Jabatan) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Periode Awal</label>
                                <input type="date" name="Periodeawal" class="form-control"
                                       value="{{ old('Periodeawal', $penugasan->Periodeawal) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Periode Akhir</label>
                                <input type="date" name="Periodeakhir" class="form-control"
                                       value="{{ old('Periodeakhir', $penugasan->Periodeakhir) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bobot (%)</label>
                            <input type="number" name="Bobot" class="form-control"
                                   min="0" max="100"
                                   value="{{ old('Bobot', $penugasan->Bobot) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Keterangan</label>
                            <textarea name="Keterangan" rows="3"
                                      class="form-control">{{ old('Keterangan', $penugasan->Keterangan) }}</textarea>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- ACTION --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('penugasan.index') }}" class="btn btn-outline-secondary px-4">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="bx bx-save me-1"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
