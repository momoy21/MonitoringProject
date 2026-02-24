{{-- Notification Bell Component --}}
<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" id="notificationDropdownToggle">
        <i class="bx bx-bell bx-sm"></i>
        <span class="badge bg-danger rounded-pill badge-notifications" id="notificationBadge" style="display: none;">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end py-0" id="notificationDropdown" style="width: 380px; max-width: 90vw;">
        {{-- Header --}}
        <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
                <h5 class="text-body mb-0 me-auto fw-bold">
                    <i class="bx bx-bell me-1" style="color: #00a0d4;"></i>Notifikasi
                </h5>
                <a href="javascript:void(0);" class="dropdown-notifications-all text-body" id="markAllReadBtn" title="Tandai semua sudah dibaca" data-bs-toggle="tooltip">
                    <i class="bx bx-check-double fs-4"></i>
                </a>
            </div>
        </li>
        
        {{-- Notification List --}}
        <li class="dropdown-notifications-list scrollable-container" style="max-height: 350px; overflow-y: auto;">
            <ul class="list-group list-group-flush" id="notificationList">
                {{-- Loading state --}}
                <li class="list-group-item text-center py-4" id="notificationLoading">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0 mt-2 text-muted small">Memuat notifikasi...</p>
                </li>
                
                {{-- Empty state (hidden by default) --}}
                <li class="list-group-item text-center py-5" id="notificationEmpty" style="display: none;">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-bell-off mb-2" style="font-size: 48px; color: #d4d4d4;"></i>
                        <p class="mb-0 text-muted">Tidak ada notifikasi baru</p>
                    </div>
                </li>
            </ul>
        </li>
        
        {{-- Footer --}}
        <li class="dropdown-menu-footer border-top">
            <a href="{{ route('notifications.index') }}" class="dropdown-item d-flex justify-content-center p-3">
                Lihat semua notifikasi
                <i class="bx bx-chevron-right ms-1"></i>
            </a>
        </li>
    </ul>
</li>

{{-- Notification Bell Styles --}}
<style>
    /* Badge Animation */
    .badge-notifications {
        position: absolute;
        top: 0;
        right: 0;
        font-size: 10px;
        min-width: 18px;
        height: 18px;
        line-height: 18px;
        padding: 0 5px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    
    /* Notification Item */
    .notification-item {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .notification-item:hover {
        background-color: rgba(0, 160, 212, 0.05) !important;
    }
    
    .notification-item.unread {
        background-color: rgba(0, 160, 212, 0.08);
    }
    
    .notification-item.type-expired {
        border-left-color: #dc3545;
    }
    
    .notification-item.type-expiring {
        border-left-color: #ffc107;
    }
    
    /* Notification Icon */
    .notification-icon {
        width: 40px;
        height: 40px;
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
    
    /* Status Badge */
    .notification-status-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Scrollbar styling */
    .dropdown-notifications-list::-webkit-scrollbar {
        width: 5px;
    }
    
    .dropdown-notifications-list::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }
    
    .dropdown-notifications-list::-webkit-scrollbar-thumb:hover {
        background: #999;
    }
    
    /* Animation for new notifications */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .notification-item {
        animation: slideIn 0.3s ease;
    }
</style>

{{-- Notification Bell Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationList = document.getElementById('notificationList');
    const notificationLoading = document.getElementById('notificationLoading');
    const notificationEmpty = document.getElementById('notificationEmpty');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const dropdownToggle = document.getElementById('notificationDropdownToggle');
    
    // Load notifications when dropdown is opened
    dropdownToggle.addEventListener('click', function() {
        loadNotifications();
    });
    
    // Initial badge count load
    loadNotificationCount();
    
    // Refresh count every 60 seconds
    setInterval(loadNotificationCount, 60000);
    
    // Mark all as read
    markAllReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        markAllAsRead();
    });
    
    function loadNotificationCount() {
        fetch('{{ route("notifications.count") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBadge(data.count);
                }
            })
            .catch(error => console.error('Error loading notification count:', error));
    }
    
    function loadNotifications() {
        // Show loading
        notificationLoading.style.display = 'block';
        notificationEmpty.style.display = 'none';
        
        // Remove existing notification items
        document.querySelectorAll('.notification-item').forEach(el => el.remove());
        
        fetch('{{ route("notifications.get") }}')
            .then(response => response.json())
            .then(data => {
                notificationLoading.style.display = 'none';
                
                if (data.success && data.notifications.length > 0) {
                    renderNotifications(data.notifications);
                    updateBadge(data.unread_count);
                } else {
                    notificationEmpty.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationLoading.style.display = 'none';
                notificationEmpty.style.display = 'block';
            });
    }
    
    function renderNotifications(notifications) {
        notifications.forEach(function(notification) {
            const item = createNotificationItem(notification);
            notificationList.insertBefore(item, notificationLoading);
        });
    }
    
    function createNotificationItem(notification) {
        const li = document.createElement('li');
        li.className = `list-group-item notification-item type-${notification.type} ${notification.is_read ? '' : 'unread'}`;
        li.setAttribute('data-id', notification.id);
        
        const iconClass = notification.type === 'expired' ? 'bx-error-circle text-danger' : 'bx-time text-warning';
        const iconBg = notification.type === 'expired' ? 'expired' : 'expiring';
        const statusClass = notification.type === 'expired' ? 'bg-danger text-white' : 'bg-warning text-dark';
        
        li.innerHTML = `
            <a href="{{ url('/notifications') }}/${notification.id}/redirect" class="d-flex text-decoration-none">
                <div class="notification-icon ${iconBg} me-3">
                    <i class="bx ${iconClass} fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-semibold text-body" style="font-size: 13px;">
                        ${notification.project_name}
                    </h6>
                    <p class="mb-1 text-muted" style="font-size: 12px;">
                        <i class="bx bx-file me-1"></i>No: ${notification.no_kontrak}
                    </p>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="notification-status-badge ${statusClass}">
                            ${notification.status_text}
                        </span>
                        <small class="text-muted">${notification.finish_kontrak}</small>
                    </div>
                </div>
            </a>
        `;
        
        return li;
    }
    
    function updateBadge(count) {
        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.style.display = 'inline-block';
        } else {
            notificationBadge.style.display = 'none';
        }
    }
    
    function markAllAsRead() {
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
                document.querySelectorAll('.notification-item.unread').forEach(el => {
                    el.classList.remove('unread');
                });
                updateBadge(0);
                
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
            }
        })
        .catch(error => console.error('Error marking all as read:', error));
    }
});
</script>
