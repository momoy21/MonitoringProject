<x-layout title="Tambah Penugasan">
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @endpush

    <div class="card shadow-sm border-0" style="max-width: 900px; margin:auto">
        <div class="card-header bg-primary text-white py-1">
            <span class="small fw-bold">Pengajuan Tim Penugasan</span>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('penugasan.store') }}" method="POST">
                @csrf

                <div class="row">
                    {{-- KIRI --}}
                    <div class="col-md-6 border-end">
                        <div class="row mb-2">
                            <label class="col-sm-4 small">ID Penugasan</label>
                            <div class="col-sm-8">: {{ $idPenugasan }}</div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-4 small">No Surat</label>
                            <div class="col-sm-8">: {{ $noSurat }}</div>
                        </div>

                        <div class="row mb-2 align-items-center">
                            <label class="col-sm-4 small">Cost Center <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <select name="Costcenter" id="select-costcenter" class="form-select form-select-sm select2" required>
                                    <option value="">Pilih Cost Center</option>
                                    @foreach($proyek as $p)
                                        <option value="{{ $p->cost_center }}" data-nama="{{ $p->Namaproject }}">
                                            {{ $p->cost_center }} - {{ $p->Namaproject }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-4 small">Nama Proyek</label>
                            <div class="col-sm-8">
                                <input type="text" id="nama-proyek" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>

                        <div class="row mb-2 align-items-center">
                            <label class="col-sm-4 small">NIK <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <select name="NIK" id="select-nik" class="form-select form-select-sm select2" required>
                                    <option value="">Pilih Karyawan</option>
                                    @foreach($karyawan as $k)
                                        <option value="{{ $k->NIK }}" data-nama="{{ $k->Nama }}">
                                            {{ $k->NIK }} - {{ $k->Nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-4 small">Nama Karyawan</label>
                            <div class="col-sm-8">
                                <input type="text" id="nama-karyawan" class="form-control form-control-sm bg-light" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN --}}
                    <div class="col-md-6 ps-4">
                        <div class="row mb-2">
                            <label class="col-sm-5 small">Tanggal Input</label>
                            <div class="col-sm-7 small">: {{ date('d/m/Y') }}</div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-5 small">Jabatan <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="text" name="Jabatan" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-5 small">Periode Awal <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="date" name="Periodeawal" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-5 small">Periode Akhir <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="date" name="Periodeakhir" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-5 small">Bobot (%) <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="number" name="Bobot" min="1" max="100"
                                       class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label class="col-sm-5 small">Keterangan</label>
                            <div class="col-sm-7">
                                <textarea name="Keterangan" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4 border-top pt-3">
                    <button class="btn btn-primary btn-sm px-5">Simpan</button>
                    <a href="{{ route('penugasan.index') }}" class="btn btn-light btn-sm ms-2">Batal</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#select-costcenter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Cost Center',
                width: '100%'
            });

            $('#select-nik').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Karyawan',
                width: '100%'
            });

            // Event Listeners using jQuery on change (compatible with Select2)
            $('#select-costcenter').on('change', function () {
                var selectedOption = $(this).find(':selected');
                var namaProyek = selectedOption.data('nama') || '';
                $('#nama-proyek').val(namaProyek);
            });

            $('#select-nik').on('change', function () {
                var selectedOption = $(this).find(':selected');
                var namaKaryawan = selectedOption.data('nama') || '';
                $('#nama-karyawan').val(namaKaryawan);
            });
        });
    </script>
    @endpush
</x-layout>