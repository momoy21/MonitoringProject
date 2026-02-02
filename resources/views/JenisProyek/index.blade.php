<x-layout title="Monitoring Project System">
    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h5 class="fw-bold">Monitoring Project System</h5>
        </div>
        <div class="input-group" style="width: 300px;">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" class="form-control" placeholder="Cari proyek atau konsumen">
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white p-2">
            <span class="small fw-bold">Daftar Pencatatan Pleno RAB</span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="bg-light text-center small">
                    <tr>
                        <th>No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Nomor IO</th>
                        <th>Cost Center</th>
                        <th>Nama Proyek</th>
                        <th>Divisi</th>
                        <th>Konsumen</th>
                        <th>Hasil Pleno</th>
                        <th>Aksi</th>
                    </tr>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th><input type="text" class="form-control form-control-sm"></th>
                        <th class="text-center">
                            <a href="{{ route('rabpleno.create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus"></i></a>
                        </th>
                    </tr>
                </thead>
                <tbody class="small">
                    @foreach($dataPleno as $index => $item)
                    <tr>
                        <td class="text-center">{{ $dataPleno->firstItem() + $index }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tglinput)->format('d/m/Y') }}</td>
                        <td>{{ $item->dokumen_io }}</td>
                        <td class="text-center">{{ $item->cost_center }}</td>
                        <td>{{ $item->namaproject }}</td>
                        <td>{{ $item->bidang_jasa }}</td>
                        <td>{{ $item->nama_konsumen }}</td>
                        <td class="text-center">
                            <span class="badge {{ $item->hasil_pleno == 'TR' ? 'bg-success' : 'bg-danger' }}">
                                {{ $item->hasil_pleno == 'TR' ? 'TERCAPAI' : 'TIDAK TERCAPAI' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('rabpleno.edit', $item->nopengajuan) }}" class="text-primary"><i class="bx bx-edit"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-2 text-end">
        <a href="#" class="small text-decoration-none text-muted"><i class="bx bx-show"></i> Lihat Detail</a>
    </div>
</x-layout>