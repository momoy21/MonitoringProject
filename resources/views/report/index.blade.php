<!DOCTYPE html>
<html>
<head>
    <title>Laporan Progress Proyek</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; background-color: #f8f9fa; padding: 20px; }
        .container { width: 1000px; margin: auto; border: 1px solid #ccc; background: #fff; padding: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        /* Header Logo & Status List */
        .header { display: flex; justify-content: space-between; padding: 15px; }
        .logo img { height: 50px; }
        .status-list { text-align: right; font-size: 10px; font-weight: bold; color: #555; line-height: 1.2; }
        
        /* Bars */
        .bar-blue { background: #5dade2; color: white; padding: 10px 15px; font-weight: bold; font-size: 12px; }
        .bar-info { background: #ebf5fb; color: #2e86c1; padding: 8px 15px; font-size: 11px; border-bottom: 1px solid #d4e6f1; }
        .bar-black { background: #333; color: white; padding: 8px 15px; font-size: 11px; font-weight: bold; }
        
        .section { padding: 15px; }
        .form-row { margin-bottom: 10px; display: flex; align-items: center; }
        label { display: inline-block; width: 150px; font-weight: bold; }
        
        .btn-proses { background: #5dade2; color: white; border: none; padding: 6px 20px; border-radius: 3px; cursor: pointer; font-weight: bold; }
        .export-tools { display: inline-flex; align-items: center; gap: 8px; margin-left: 15px; }
        .export-tools img { width: 18px; height: 18px; cursor: pointer; border: 1px solid #ddd; padding: 2px; border-radius: 2px; }

        /* Table Style */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f2f2f2; border: 1px solid #000; padding: 8px; text-align: center; }
        td { border: 1px solid #000; padding: 8px; vertical-align: top; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        @media print {
            .no-print { display: none !important; }
            .container { width: 100%; border: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo">
            <img src="https://images.contactout.com/companies/6be54379523a70a5c783c5cdc31fb2d2" alt="Logo">
        </div>
        <div class="status-list">DRAFT<br>REVIEW<br>APPROVE<br>PENDING</div>
    </div>

    <div class="bar-blue">Laporan Progress Proyek</div>

    <form method="GET" action="{{ url()->current() }}" class="no-print">
        <div class="section">
            <div class="form-row">
                <label>Jenis Report</label>
                <input type="radio" name="jenis_report" value="berita_acara" id="ba" {{ request('jenis_report') != 'issue_project' ? 'checked' : '' }} onchange="this.form.submit()"> 
                <label for="ba" style="width:auto; margin: 0 20px 0 5px;">Berita Acara</label>
                
                <input type="radio" name="jenis_report" value="issue_project" id="issue" {{ request('jenis_report') == 'issue_project' ? 'checked' : '' }} onchange="this.form.submit()"> 
                <label for="issue" style="width:auto; margin-left:5px;">Issue Proyek</label>
            </div>

            <div class="bar-info">
                Jika pilihan jenis report Progress {{ request('jenis_report') == 'issue_project' ? 'Issue Proyek' : 'Berita Acara' }}
            </div>

            <div class="form-row" style="margin-top:15px;">
                <label>Status {{ request('jenis_report') == 'issue_project' ? 'Issue Proyek' : 'Berita Acara' }}</label>
                <input type="radio" name="status_radio" value="All" {{ request('status') == 'All' || !request('status') ? 'checked' : '' }}>
                <label style="width:auto; margin: 0 10px 0 5px;">All</label>
                
                <select name="status" style="padding: 3px; width: 120px;">
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

            <div class="form-row">
                <label>Periode</label>
                <input type="date" name="start" value="{{ request('start') }}"> 
                <span style="margin: 0 10px;">s.d</span> 
                <input type="date" name="end" value="{{ request('end') }}">
                
                <button type="submit" class="btn-proses" style="margin-left:15px;">Proses</button>

                <<div class="export-tools">
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
        </div>
    </form>

    <div class="bar-black">
        Laporan Progress {{ request('jenis_report') == 'issue_project' ? 'Issue Proyek' : 'Berita Acara' }} 
        {{ request('status') && request('status') != 'All' ? ' ('.request('status').')' : '' }}
    </div>

    <div class="section">
        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="text-transform: uppercase; margin-bottom: 5px;">LAPORAN PROGRESS {{ request('jenis_report') == 'issue_project' ? 'ISSUE PROYEK' : 'BERITA ACARA' }}</h3>
            <p>Periode : {{ request('start') ? date('d/m/Y', strtotime(request('start'))) : '..........' }} s.d {{ request('end') ? date('d/m/Y', strtotime(request('end'))) : '..........' }}</p>
        </div>

        <table>
            <thead>
                @if(request('jenis_report') == 'issue_project')
                    <tr>
                        <th width="30">No</th>
                        <th width="100">Tanggal</th>
                        <th>Issue / Kendala</th>
                        <th>Mitigasi Issue</th>
                        <th width="80">Status</th>
                    </tr>
                @else
                    <tr>
                        <th width="30">No</th>
                        <th width="100">Tanggal</th>
                        <th>Nama Proyek</th>
                        <th>NO Dokumen</th>
                        <th width="120">Nilai BA</th>
                    </tr>
                @endif
            </thead>
            <tbody>
                @forelse($data as $key => $item)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        @if(request('jenis_report') == 'issue_project')
                            <td class="text-center">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                            <td>{{ $item->issue }}</td>
                            <td>{{ $item->mitigasi }}</td>
                            <td class="text-center">{{ $item->status == 'O' ? 'Open' : 'Close' }}</td>
                        @else
                            <td class="text-center">{{ date('d/m/Y', strtotime($item->periode_mulai)) }}</td>
                            <td>{{ $item->namaproject }}</td>
                            <td class="text-center">{{ $item->no_ba }}</td>
                            <td class="text-right">Rp {{ number_format($item->nilai_ba, 0, ',', '.') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 30px; color: #999;">Data tidak ditemukan. Silakan isi filter dan klik Proses.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</body>
</html>