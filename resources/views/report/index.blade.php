<x-layout>
    <x-slot:title>Laporan Progress Proyek</x-slot:title>

    {{-- Breadcrumbs --}}
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Laporan Proyek', 'url' => '#', 'active' => true]
        ];
    @endphp

    <style>
        .report-card { background: #fff; border-radius: 8px; border: 1px solid #d9dee3; overflow: hidden; }
        .bar-blue { background: #5dade2; color: white; padding: 12px 20px; font-weight: bold; font-size: 14px; }
        .bar-info { background: #ebf5fb; color: #2e86c1; padding: 10px 20px; font-size: 13px; border-bottom: 1px solid #d4e6f1; }
        .bar-black { background: #333; color: white; padding: 10px 20px; font-size: 13px; font-weight: bold; }
        
        .report-content { padding: 25px; }
        .form-row-custom { margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .form-row-custom label { width: 160px; font-weight: 600; color: #566a7f; margin-bottom: 0; }
        
        .export-tools { display: inline-flex; align-items: center; gap: 10px; margin-left: 15px; }
        .export-tools img { width: 22px; height: 22px; cursor: pointer; border: 1px solid #d9dee3; padding: 4px; border-radius: 4px; background: #fff; transition: 0.2s; }
        .export-tools img:hover { background: #f5f5f9; border-color: #696cff; }

        .table-report { width: 100%; border-collapse: collapse; margin-top: 20px; color: #333; }
        .table-report th { background: #f5f5f9 !important; border: 1px solid #444 !important; padding: 10px; text-align: center; font-weight: 700; text-transform: uppercase; }
        .table-report td { border: 1px solid #444 !important; padding: 10px; vertical-align: top; }
        
        @media print {
            .no-print, .layout-menu, .layout-navbar, .content-footer, .btn-primary { display: none !important; }
            .layout-page { padding: 0 !important; }
            .container-xxl { padding: 0 !important; max-width: 100% !important; }
            .report-card { border: none; }
            body { background: white !important; }
        }
    </style>

    <div class="report-card">
        <div class="bar-blue">Laporan Progress Proyek</div>

        <div class="report-content">
            {{-- Filter Section --}}
            <form method="GET" action="{{ url()->current() }}" class="no-print mb-4">
                <div class="form-row-custom">
                    <label>Jenis Report</label>
                    <div class="d-flex align-items-center">
                        <input type="radio" name="jenis_report" value="berita_acara" id="ba" {{ request('jenis_report') != 'issue_project' ? 'checked' : '' }} onchange="this.form.submit()"> 
                        <label for="ba" class="ms-1 me-4" style="width:auto;">Berita Acara</label>
                        
                        <input type="radio" name="jenis_report" value="issue_project" id="issue" {{ request('jenis_report') == 'issue_project' ? 'checked' : '' }} onchange="this.form.submit()"> 
                        <label for="issue" class="ms-1" style="width:auto;">Issue Proyek</label>
                    </div>
                </div>

                <div class="bar-info mb-4">
                    Jika pilihan jenis report Progress {{ request('jenis_report') == 'issue_project' ? 'Issue Proyek' : 'Berita Acara' }}
                </div>

                <div class="form-row-custom">
                    <label>Status</label>
                    <select name="status" class="form-select form-select-sm" style="width: 220px;">
                        <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All</option>
                        @if(request('jenis_report') == 'issue_project')
                            <option value="O" {{ request('status') == 'O' ? 'selected' : '' }}>Open</option>
                            <option value="C" {{ request('status') == 'C' ? 'selected' : '' }}>Close</option>
                        @else
                            <option value="01" {{ request('status') == '01' ? 'selected' : '' }}>Draft</option>
                            <option value="02" {{ request('status') == '02' ? 'selected' : '' }}>Review</option>
                            <option value="03" {{ request('status') == '03' ? 'selected' : '' }}>Approve</option>
                        @endif
                    </select>
                </div>

                <div class="form-row-custom">
                    <label>Periode</label>
                    <input type="date" name="start" class="form-control form-control-sm w-auto" value="{{ request('start') }}"> 
                    <span class="mx-2">s.d</span> 
                    <input type="date" name="end" class="form-control form-control-sm w-auto" value="{{ request('end') }}">
                    
                    <button type="submit" class="btn btn-sm btn-primary ms-3">Proses</button>

                    <div class="export-tools">
                        <a href="{{ route('report.pdf', request()->all()) }}" target="_blank" title="Export PDF">
                            <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png">
                        </a>
                        <a href="{{ route('report.excel', request()->all()) }}" title="Export Excel">
                            <img src="https://cdn-icons-png.flaticon.com/512/732/732220.png">
                        </a>
                        <img src="https://cdn-icons-png.flaticon.com/512/446/446991.png" onclick="window.print()" title="Print">
                        <a href="{{ route('report.index') }}" title="Refresh">
                            <img src="https://cdn-icons-png.flaticon.com/512/2267/2267918.png">
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table Title --}}
            <div class="bar-black" style="margin-left: -25px; margin-right: -25px;">
                Laporan Progress {{ request('jenis_report') == 'issue_project' ? 'Issue Proyek' : 'Berita Acara' }} 
                {{ request('status') && request('status') != 'All' ? ' ('.request('status').')' : '' }}
            </div>

            <div class="text-center mt-5 mb-4">
                <h4 class="text-uppercase fw-bold mb-1">LAPORAN PROGRESS {{ request('jenis_report') == 'issue_project' ? 'ISSUE PROYEK' : 'BERITA ACARA' }}</h4>
                <p class="mb-0">Periode : {{ request('start') ? date('d/m/Y', strtotime(request('start'))) : '..........' }} s.d {{ request('end') ? date('d/m/Y', strtotime(request('end'))) : '..........' }}</p>
            </div>

            {{-- Table Data --}}
            <table class="table-report">
                <thead>
                    @if(request('jenis_report') == 'issue_project')
                        <tr>
                            <th width="50">No</th>
                            <th width="130">Tanggal</th>
                            <th>Nama Proyek</th> {{-- Tambahan kolom --}}
                            <th>Issue / Kendala</th>
                            <th>Mitigasi Issue</th>
                            <th width="100">Status</th>
                        </tr>
                    @else
                        <tr>
                            <th width="50">No</th>
                            <th width="130">Tanggal</th>
                            <th>Nama Proyek</th>
                            <th>NO Dokumen</th>
                            <th width="180">Nilai BA</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($data as $key => $item)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            @if(request('jenis_report') == 'issue_project')
                                <td class="text-center">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                                <td>{{ $item->namaproject }}</td> {{-- Tambahan data nama proyek --}}
                                <td>{{ $item->issue }}</td>
                                <td>{{ $item->mitigasi }}</td>
                                <td class="text-center">{{ $item->status == 'O' ? 'Open' : 'Close' }}</td>
                            @else
                                <td class="text-center">{{ date('d/m/Y', strtotime($item->periode_mulai)) }}</td>
                                <td>{{ $item->namaproject }}</td>
                                <td class="text-center">{{ $item->no_ba }}</td>
                                <td class="text-end">Rp {{ number_format($item->nilai_ba, 0, ',', '.') }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            {{-- Colspan disesuaikan menjadi 6 untuk issue_project atau 5 untuk BA --}}
                            <td colspan="{{ request('jenis_report') == 'issue_project' ? 6 : 5 }}" class="text-center py-5 text-muted">Data tidak ditemukan. Silakan isi filter dan klik Proses.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>