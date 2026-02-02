<x-layout title="Edit Hasil Pleno">
    <div class="nonsticky-header mb-4">
        <h4 class="fw-bold">Input Hasil Pleno: {{ $item->nopengajuan }}</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('rabpleno.update', $item->nopengajuan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Margin Pleno (%)</label>
                        <input type="number" step="0.01" name="marginpleno" class="form-control" value="{{ $item->marginpleno }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Progress Akhir</label>
                        <select name="progress" id="progress_select" class="form-select">
                            <option value="01" {{ $item->progress == '01' ? 'selected' : '' }}>[01] Belum Diterima</option>
                            <option value="02" {{ $item->progress == '02' ? 'selected' : '' }}>[02] Proses Tanda Tangan</option>
                            <option value="04" {{ $item->progress == '04' ? 'selected' : '' }}>[04] Done</option>
                        </select>
                    </div>
                </div>

                <div id="upload_field" class="mb-3 {{ $item->progress == '04' ? '' : 'd-none' }}">
                    <label class="form-label text-danger fw-bold">Upload RAB Final (Wajib Jika Done)</label>
                    <input type="file" name="hasilupload" class="form-control">
                    @if($item->hasilupload)
                        <small class="text-muted">File saat ini: <a href="{{ asset('uploads/rab_pleno/'.$item->hasilupload) }}" target="_blank">{{ $item->hasilupload }}</a></small>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Catatan/Komentar</label>
                    <textarea name="catatan" class="form-control" rows="3">{{ $item->catatan }}</textarea>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary px-5">Update Data Pleno</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('progress_select').addEventListener('change', function() {
            const uploadField = document.getElementById('upload_field');
            if (this.value == '04') {
                uploadField.classList.remove('d-none');
            } else {
                uploadField.classList.add('d-none');
            }
        });
    </script>
    @endpush
</x-layout>