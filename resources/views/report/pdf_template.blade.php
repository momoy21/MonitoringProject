<!DOCTYPE html>
<html>
<head>
    <title>Laporan Progress Proyek</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table, th, td { border: 1px solid black; }
        th { background-color: #f2f2f2; padding: 8px; }
        td { padding: 5px; vertical-align: top; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h2>LAPORAN PROGRESS {{ $jenis == 'berita_acara' ? 'BERITA ACARA' : 'ISSUE PROYEK' }}</h2>
    </div>

    <table>
        <thead>
            @if($jenis == 'berita_acara')
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th>Nama Proyek</th>
                    <th width="20%">No Dokumen</th>
                    <th width="15%">Nilai BA</th>
                </tr>
            @else
                <tr>
                    <th width="5%">No</th>
                    <th width="10%">Tanggal</th>
                    <th>Nama Proyek</th>
                    <th>Issue / Kendala</th>
                    <th>Mitigasi</th>
                    <th width="10%">Status</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($data as $key => $row)
                <tr>
                    <td class="text-center">{{ $key + 1 }}</td>
                    @if($jenis == 'berita_acara')
                        <td>{{ $row->periode_mulai }}</td>
                        <td>{{ $row->namaproject }}</td>
                        <td>{{ $row->no_ba }}</td>
                        <td>Rp {{ number_format($row->nilai_ba, 0, ',', '.') }}</td>
                    @else
                        <td>{{ $row->tanggal }}</td>
                        <td>{{ $row->namaproject }}</td>
                        <td>{{ $row->issue }}</td>
                        <td>{{ $row->mitigasi }}</td>
                        <td class="text-center">{{ $row->status == 'O' ? 'Open' : 'Close' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>