<x-layout title="Edit Divisi">
    <x-slot name="breadcrumbs">
        @php
        $breadcrumbs = [['name' => 'Master Divisi', 'url' => route('masterdivisi.index')], ['name' => 'Edit Divisi']];
        @endphp
    </x-slot>
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="nonsticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Edit Divisi: {{ $divisi->kode_divisi }}</h4>
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
            <form action="{{ route('masterdivisi.update', $divisi->kode_divisi) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Divisi</label>
                        <input type="text" class="form-control bg-light" value="{{ $divisi->kode_divisi }}" readonly>
                        <small class="text-muted">Kode divisi tidak dapat diubah.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Divisi</label>
                        <input type="text" name="nama_divisi" class="form-control" maxlength="100" value="{{ old('nama_divisi', $divisi->nama_divisi) }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="A" {{ old('status', $divisi->status) == 'A' ? 'selected' : '' }}>Aktif</option>
                            <option value="N" {{ old('status', $divisi->status) == 'N' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>