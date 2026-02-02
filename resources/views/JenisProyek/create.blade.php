<x-layout title="Tambah Pengajuan RAB">
    <form action="{{ route('rabpleno.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Dokumen IO</label>
                        <input type="text" name="dokumen_io" class="form-control" maxlength="9">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Cost Center <span class="text-danger">*</span></label>
                        <input type="text" name="cost_center" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Divisi</label>
                        <select name="divisi" class="form-select">
                            <option value="DT">Digital Transformation</option>
                            <option value="ERP">ERP</option>
                            <option value="Infra">Infrastruktur</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Nama Proyek <span class="text-danger">*</span></label>
                    <input type="text" name="namaproject" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Bidang Jasa</label>
                        <select name="bidang_jasa" class="form-select">
                            <option value="01">SAP</option>
                            <option value="02">Pengelolaan System</option>
                            <option value="05">Infrastructure IT</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Project Manager (PM)</label>
                        <input type="text" name="pm" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Margin RKAP (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="marginrkap" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Jenis Pleno</label>
                        <select name="keterangan" class="form-select">
                            <option value="P">Pleno</option>
                            <option value="T">Tidak Pleno</option>
                            <option value="R">Revisi RAB</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Progress</label>
                        <select name="progress" class="form-select">
                            <option value="01">[01] Dokumen belum diterima</option>
                            <option value="02">[02] Proses tanda tangan BOD</option>
                            <option value="04">[04] Done</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Pengajuan</button>
            </div>
        </div>
    </form>
</x-layout>