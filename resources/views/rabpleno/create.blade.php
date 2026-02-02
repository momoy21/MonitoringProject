<x-layout title="Tambah Pengajuan RAB">
    <div class="nonsticky-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">Tambah Pengajuan Baru</h4>
                <p class="mb-0">Daftarkan pengajuan proyek baru</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('rabpleno.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('rabpleno.store') }}" method="POST">
                @csrf
                <div class="form-section">
                    <h6 class="mb-3 text-primary border-bottom pb-2">Informasi Dasar Proyek</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No Pengajuan</label>
                            <input type="text" class="form-control bg-light" placeholder="Otomatis (YYYYMMXX)" readonly disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cost Center <span class="text-danger">*</span></label>
                            <input type="text" name="cost_center" class="form-control" maxlength="15" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Proyek <span class="text-danger">*</span></label>
                        <input type="text" name="namaproject" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konsumen <span class="text-danger">*</span></label>
                            <select name="idkonsumen" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                @foreach($konsumens as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_konsumen }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nilai Proyek (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="nilaiproyek" class="form-control" required>
                        </div>
                    </div>

                    <h6 class="mb-3 text-primary border-bottom pb-2 mt-4">Parameter Pleno</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Margin RKAP (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="marginrkap" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jenis Pleno <span class="text-danger">*</span></label>
                            <select name="keterangan" class="form-select" required>
                                <option value="P">Pleno</option>
                                <option value="T">Tidak Pleno</option>
                                <option value="R">Revisi RAB</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="progress" class="form-select" required>
                                <option value="01">[01] Dokumen Belum Diterima</option>
                                <option value="02">[02] Proses Tanda Tangan BOD</option>
                                <option value="04">[04] Done</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-5">Simpan Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>