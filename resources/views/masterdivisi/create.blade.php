<x-layout title="Tambah Divisi">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [['name' => 'Master Divisi', 'url' => route('masterdivisi.index')], ['name' => 'Tambah Divisi']];
        @endphp
    </x-slot>
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Divisi Baru</h4>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('masterdivisi.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('masterdivisi.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Divisi <span class="text-danger">*</span></label>
                        <input type="text" name="kode_divisi" class="form-control @error('kode_divisi') is-invalid @enderror" maxlength="10" required value="{{ old('kode_divisi') }}">
                        @error('kode_divisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Divisi</label>
                        <input type="text" name="nama_divisi" class="form-control" maxlength="100" value="{{ old('nama_divisi') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="A" {{ old('status') == 'A' ? 'selected' : '' }}>Aktif</option>
                            <option value="N" {{ old('status') == 'N' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-outline-warning">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>