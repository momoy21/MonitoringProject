<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Kontrak Proyek</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #00a0d4 0%, #0077a8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .subtitle {
            margin-top: 8px;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 0 4px 4px 0;
        }
        .alert-box.danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .project-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        .project-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .project-header.expired {
            background-color: #f8d7da;
        }
        .project-header.expiring {
            background-color: #fff3cd;
        }
        .project-name {
            font-weight: 600;
            font-size: 16px;
            margin: 0;
            color: #333;
        }
        .project-body {
            padding: 15px;
        }
        .project-detail {
            display: flex;
            margin-bottom: 8px;
        }
        .project-detail-label {
            width: 120px;
            color: #666;
            font-size: 13px;
        }
        .project-detail-value {
            flex: 1;
            font-weight: 500;
            font-size: 13px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-badge.expired {
            background-color: #dc3545;
            color: white;
        }
        .status-badge.expiring {
            background-color: #ffc107;
            color: #333;
        }
        .action-btn {
            display: inline-block;
            background: linear-gradient(135deg, #00a0d4 0%, #0077a8 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e0e0e0;
        }
        .footer a {
            color: #00a0d4;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Notifikasi Kontrak Proyek</h1>
            <div class="subtitle">Monitoring Project System - PT KIT</div>
        </div>
        
        <div class="content">
            <div class="greeting">
                Halo <strong>{{ $user->name }}</strong>,
            </div>
            
            <div class="alert-box {{ $notifications->where('type', 'expired')->count() > 0 ? 'danger' : '' }}">
                @php
                    $expiredCount = $notifications->where('type', 'expired')->count();
                    $expiringCount = $notifications->where('type', 'expiring')->count();
                @endphp
                
                @if($expiredCount > 0 && $expiringCount > 0)
                    Terdapat <strong>{{ $expiredCount }} kontrak telah habis</strong> dan 
                    <strong>{{ $expiringCount }} kontrak akan segera berakhir</strong> yang memerlukan perhatian Anda.
                @elseif($expiredCount > 0)
                    Terdapat <strong>{{ $expiredCount }} kontrak proyek</strong> yang telah melewati tanggal berakhir.
                @else
                    Terdapat <strong>{{ $expiringCount }} kontrak proyek</strong> yang akan segera berakhir dalam 30 hari.
                @endif
            </div>
            
            @foreach($notifications as $notification)
                <div class="project-card">
                    <div class="project-header {{ $notification->type }}">
                        <p class="project-name">{{ $notification->project->namaproject ?? 'N/A' }}</p>
                    </div>
                    <div class="project-body">
                        <div class="project-detail">
                            <span class="project-detail-label">No. Kontrak</span>
                            <span class="project-detail-value">{{ $notification->no_kontrak ?: '-' }}</span>
                        </div>
                        <div class="project-detail">
                            <span class="project-detail-label">Tanggal Berakhir</span>
                            <span class="project-detail-value">{{ $notification->formatted_finish_date }}</span>
                        </div>
                        <div class="project-detail">
                            <span class="project-detail-label">Status</span>
                            <span class="project-detail-value">
                                <span class="status-badge {{ $notification->type }}">
                                    {{ $notification->status_text }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
            
            <div style="text-align: center;">
                <a href="{{ url('/dataproyek') }}" class="action-btn">
                    Lihat Detail Proyek
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem Monitoring Project PT KIT.</p>
            <p>Jika ada pertanyaan, silakan hubungi <a href="mailto:support@kit.co.id">support@kit.co.id</a></p>
        </div>
    </div>
</body>
</html>
