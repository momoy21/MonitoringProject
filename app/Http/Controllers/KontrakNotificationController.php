<?php

namespace App\Http\Controllers;

use App\Services\KontrakNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KontrakNotificationController extends Controller
{
    protected KontrakNotificationService $notificationService;

    public function __construct(KontrakNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notifications for dropdown (AJAX)
     */
    public function getNotifications(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notifications = $this->notificationService->getUnreadNotifications($user->id, 5);
        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'id_project' => $notification->id_project,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'project_name' => $notification->project->namaproject ?? 'N/A',
                    'no_kontrak' => $notification->no_kontrak ?: '-',
                    'finish_kontrak' => $notification->formatted_finish_date,
                    'status_text' => $notification->status_text,
                    'badge_class' => $notification->badge_class,
                    'icon_class' => $notification->icon_class,
                    'time_ago' => $notification->time_ago,
                    'is_read' => $notification->is_read,
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get all notifications (paginated)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notifications = $this->notificationService->getAllNotifications($user->id, 15);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $success = $this->notificationService->markAsRead($id, $user->id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notifikasi ditandai sudah dibaca' : 'Notifikasi tidak ditemukan',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $count = $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} notifikasi ditandai sudah dibaca",
        ]);
    }

    /**
     * Get unread count only (for polling/realtime updates)
     */
    public function getUnreadCount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $count = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Redirect to project and mark notification as read
     */
    public function redirectToProject(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Mark as read
        $this->notificationService->markAsRead($id, $user->id);

        // Get notification to get project ID
        $notification = \App\Models\KontrakNotification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            // Redirect to data proyek with highlight parameter
            return redirect()->route('dataproyek.index', [
                'search' => $notification->id_project,
                'highlight' => $notification->id_project,
            ]);
        }

        return redirect()->route('dataproyek.index');
    }
}
