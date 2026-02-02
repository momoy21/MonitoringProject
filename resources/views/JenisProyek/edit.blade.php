<x-layout title="Pencatatan Hasil Pleno RAB">
    <div class="card border-primary" style="max-width: 900px; margin: auto;">
        <div class="card-header bg-primary text-white p-2">
            <h6 class="mb-0">Pencatatan Hasil Pleno RAB</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('rabpleno.update', $item->nopengajuan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="row mb-2">
                            <label class="col-sm-4 small">NO Pengajuan</label>
                            <div class="col-sm-8 small">: **{{ $item->nopengajuan }}**</div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Dokumen IO</label>
                            <div class="col-sm-8"><input type="text" name="dokumen_io" class="form-control form-control-sm" value="{{ $item->dokumen_io }}"></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Cost Center</label>
                            <div class="col-sm-8"><input type="text" name="cost_center" class="form-control form-control-sm" value="{{ $item->cost_center }}"></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Nama Proyek</label>
                            <div class="col-sm-8"><input type="text" name="namaproject" class="form-control form-control-sm" value="{{ $item->namaproject }}"></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Progress</label>
                            <div class="col-sm-8">
                                <select name="progress" class="form-select form-select-sm">
                                    <option value="01" {{ $item->progress == '01' ? 'selected' : '' }}>01</option>
                                    <option value="04" {{ $item->progress == '04' ? 'selected' : '' }}>04 (Done)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Tanggal Pengajuan</label>
                            <div class="col-sm-8 small">: {{ \Carbon\Carbon::parse($item->tglinput)->format('d/m/Y') }}</div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Konsumen</label>
                            <div class="col-sm-8">
                                <select name="idkonsumen" class="form-select form-select-sm">
                                    @foreach($konsumens as $k)
                                        <option value="{{ $k->id }}" {{ $item->idkonsumen == $k->id ? 'selected' : '' }}>{{ $k->nama_konsumen }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Bidang Jasa</label>
                            <div class="col-sm-8"><input type="text" name="bidang_jasa" class="form-control form-control-sm" value="{{ $item->bidang_jasa }}"></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Hasil Pleno</label>
                            <div class="col-sm-8">
                                <select class="form-select form-select-sm" disabled>
                                    <option>{{ $item->hasil_pleno == 'TR' ? 'TERCAPAI' : 'TIDAK TERCAPAI' }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Margin RKAP</label>
                            <div class="col-sm-8"><input type="number" name="marginrkap" class="form-control form-control-sm" value="{{ $item->marginrkap }}"></div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 small">Margin Pleno</label>
                            <div class="col-sm-8"><input type="number" name="marginpleno" class="form-control form-control-sm" value="{{ $item->marginpleno }}"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small">Dokumen RAB Final</label>
                    <input type="file" name="hasilupload" class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label class="small">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="3">{{ $item->catatan }}</textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-5">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-layout>