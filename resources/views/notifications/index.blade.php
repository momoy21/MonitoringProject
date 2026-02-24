<x-layout title="Notifikasi Kontrak">
    <!-- Header Section -->
    <div class="sticky-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="fw-bold mb-2">
                    <i class="bx bx-bell me-2" style="color: #00a0d4;"></i>Notifikasi Kontrak
                </h4>
                <p class="mb-0 text-muted">Daftar notifikasi kontrak proyek yang memerlukan perhatian</p>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline-primary" id="markAllReadBtn">
                    <i class="bx bx-check-double me-1"></i> Tandai Semua Dibaca
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        @php
            $expiredCount = $notifications->where('type', 'expired')->count();
            $expiringCount = $notifications->where('type', 'expiring')->count();
            $unreadCount = $notifications->where('is_read', false)->count();
        @endphp
        
        <div class="col-md-4">
            <div class="card border-start border-danger border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg" style="background: rgba(220, 53, 69, 0.1); border-radius: 50%;">
                                <i class="bx bx-error-circle text-danger" style="font-size: 28px; line-height: 50px; margin-left: 11px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $expiredCount }}</h3>
                            <span class="text-muted">Kontrak Habis</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg" style="background: rgba(255, 193, 7, 0.1); border-radius: 50%;">
                                <i class="bx bx-time text-warning" style="font-size: 28px; line-height: 50px; margin-left: 11px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $expiringCount }}</h3>
                            <span class="text-muted">Akan Berakhir</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg" style="background: rgba(0, 160, 212, 0.1); border-radius: 50%;">
                                <i class="bx bx-envelope text-primary" style="font-size: 28px; line-height: 50px; margin-left: 11px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $unreadCount }}</h3>
                            <span class="text-muted">Belum Dibaca</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification List -->
    <div class="card">
        <div class="card-body p-0">
            @if($notifications->count() > 0)
                <div class="list-group list-group-flush" id="notificationListFull">
                    @foreach($notifications as $notification)
                        <div class="list-group-item notification-list-item {{ $notification->is_read ? '' : 'unread' }}" 
                             data-id="{{ $notification->id }}"
                             style="border-left: 4px solid {{ $notification->type === 'expired' ? '#dc3545' : '#ffc107' }};">
                            <a href="{{ route('notifications.redirect', $notification->id) }}" class="d-flex text-decoration-none">
                                <div class="notification-icon {{ $notification->type === 'expired' ? 'expired' : 'expiring' }} me-3">
                                    <i class="bx {{ $notification->type === 'expired' ? 'bx-error-circle text-danger' : 'bx-time text-warning' }} fs-3"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 fw-semibold text-body">{{ $notification->project->namaproject ?? 'N/A' }}</h6>
                                        <span class="badge {{ $notification->type === 'expired' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ $notification->status_text }}
                                        </span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="bx bx-file me-1"></i>
                                                <strong>No. Kontrak:</strong> {{ $notification->no_kontrak ?: '-' }}
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="bx bx-calendar me-1"></i>
                                                <strong>Tanggal Berakhir:</strong> {{ $notification->formatted_finish_date }}
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">
                                                <i class="bx bx-time-five me-1"></i>
                                                {{ $notification->time_ago }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="align-self-center ms-3">
                                    <i class="bx bx-chevron-right text-muted fs-4"></i>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center py-3">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-bell-off mb-2" style="font-size: 64px; color: #d4d4d4;"></i>
                        <h5 class="mt-3 text-muted">Tidak ada notifikasi</h5>
                        <p class="text-muted">Semua kontrak proyek Anda dalam kondisi baik</p>
                        <a href="{{ route('dataproyek.index') }}" class="btn btn-primary">
                            <i class="bx bx-folder me-1"></i> Lihat Data Proyek
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .notification-list-item {
            transition: all 0.2s ease;
        }
        
        .notification-list-item:hover {
            background-color: rgba(0, 160, 212, 0.05) !important;
        }
        
        .notification-list-item.unread {
            background-color: rgba(0, 160, 212, 0.08);
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .notification-icon.expired {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .notification-icon.expiring {
            background-color: rgba(255, 193, 7, 0.1);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
            fetch('{{ route("notifications.read-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    document.querySelectorAll('.notification-list-item.unread').forEach(el => {
                        el.classList.remove('unread');
                    });
                    
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                    
                    // Reload to refresh stats
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
    @endpush
</x-layout>
