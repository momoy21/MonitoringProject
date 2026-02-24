<?php

namespace App\Services;

use App\Models\DataProyek;
use App\Models\HistoryProyek;
use App\Models\KontrakNotification;
use App\Models\User;
use App\Mail\KontrakExpiredMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class KontrakNotificationService
{
    /**
     * Status proyek yang akan di-notifikasi
     */
    const NOTIFIABLE_STATUS = ['I', 'O']; // In Progress dan Open/Aktif

    /**
     * Jumlah hari sebelum expire untuk peringatan
     */
    const WARNING_DAYS = 30;

    /**
     * Check dan update status kontrak untuk semua proyek
     */
    public function checkAndUpdateKontrakStatus(): array
    {
        $today = Carbon::today();
        $warningDate = $today->copy()->addDays(self::WARNING_DAYS);

        $result = [
            'expired' => 0,
            'expiring' => 0,
            'notifications_created' => 0,
            'emails_sent' => 0,
            'errors' => [],
        ];

        try {
            // Get all projects with notifiable status
            $projects = DataProyek::whereIn('status', self::NOTIFIABLE_STATUS)
                ->whereNotNull('finish_kontrak')
                ->get();

            foreach ($projects as $project) {
                try {
                    $finishDate = Carbon::parse($project->finish_kontrak);
                    $oldStatus = $project->kontrak_status;
                    $newStatus = null;

                    // Determine contract status
                    if ($today->gt($finishDate)) {
                        // Kontrak sudah berakhir
                        $newStatus = 'B';
                        $result['expired']++;
                    } elseif ($today->gte($finishDate->copy()->subDays(self::WARNING_DAYS))) {
                        // Kontrak akan berakhir dalam 30 hari
                        $newStatus = 'A';
                        $result['expiring']++;
                    }

                    // Update status if changed
                    if ($newStatus !== $oldStatus) {
                        $project->update(['kontrak_status' => $newStatus]);

                        // Also update latest history_proyek record
                        $this->updateHistoryProyekStatus($project->id_project, $newStatus);
                    }

                    // Create notifications if status needs notification
                    if ($newStatus && $this->shouldCreateNotification($project, $newStatus)) {
                        $notifCount = $this->createNotificationsForProject($project, $newStatus);
                        $result['notifications_created'] += $notifCount;
                    }
                } catch (\Exception $e) {
                    $result['errors'][] = "Project {$project->id_project}: " . $e->getMessage();
                    Log::error('Error processing project for kontrak notification', [
                        'id_project' => $project->id_project,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Send pending emails
            $result['emails_sent'] = $this->sendPendingEmails();

        } catch (\Exception $e) {
            $result['errors'][] = 'General error: ' . $e->getMessage();
            Log::error('Error in kontrak notification service', [
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Update status kontrak di history_proyek
     */
    protected function updateHistoryProyekStatus(string $idProject, ?string $status): void
    {
        HistoryProyek::where('id_project', $idProject)
            ->orderBy('norut', 'desc')
            ->limit(1)
            ->update(['kontrak_status' => $status]);
    }

    /**
     * Check if notification should be created (avoid duplicates)
     * 
     * Logic:
     * - Untuk tipe 'expired': Hanya buat 1x notifikasi per proyek (selamanya)
     * - Untuk tipe 'expiring': Hanya buat 1x notifikasi per proyek (selamanya)
     * - Jika proyek sudah punya notifikasi expired, tidak perlu notifikasi expiring lagi
     */
    protected function shouldCreateNotification(DataProyek $project, string $type): bool
    {
        $notifType = $type === 'B' ? KontrakNotification::TYPE_EXPIRED : KontrakNotification::TYPE_EXPIRING;

        // Jika sudah ada notifikasi expired untuk proyek ini, skip semua notifikasi baru
        if ($type === 'A') {
            $hasExpiredNotif = KontrakNotification::where('id_project', $project->id_project)
                ->where('type', KontrakNotification::TYPE_EXPIRED)
                ->exists();
            
            if ($hasExpiredNotif) {
                return false; // Sudah expired, tidak perlu notifikasi "akan berakhir"
            }
        }

        // Check if notification already exists for this project and type (ever, not just today)
        $exists = KontrakNotification::where('id_project', $project->id_project)
            ->where('type', $notifType)
            ->exists();

        return !$exists;
    }

    /**
     * Create notifications for all eligible users
     */
    protected function createNotificationsForProject(DataProyek $project, string $statusType): int
    {
        $notifType = $statusType === 'B' ? KontrakNotification::TYPE_EXPIRED : KontrakNotification::TYPE_EXPIRING;
        $title = $statusType === 'B' ? 'Kontrak Telah Habis' : 'Kontrak Akan Berakhir';
        
        $message = $statusType === 'B' 
            ? "Kontrak proyek {$project->namaproject} telah berakhir pada " . Carbon::parse($project->finish_kontrak)->format('d/m/Y')
            : "Kontrak proyek {$project->namaproject} akan berakhir pada " . Carbon::parse($project->finish_kontrak)->format('d/m/Y');

        $count = 0;

        // Get users who have access to this project's bidang jasa
        $users = $this->getEligibleUsers($project);

        foreach ($users as $user) {
            // Avoid duplicate notification for same user/project/type (ever, not just today)
            $exists = KontrakNotification::where('id_project', $project->id_project)
                ->where('user_id', $user->id)
                ->where('type', $notifType)
                ->exists();

            if (!$exists) {
                KontrakNotification::create([
                    'id_project' => $project->id_project,
                    'user_id' => $user->id,
                    'type' => $notifType,
                    'title' => $title,
                    'message' => $message,
                    'no_kontrak' => $project->no_kontrak,
                    'finish_kontrak' => $project->finish_kontrak,
                    'is_read' => false,
                    'email_sent' => false,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get users eligible to receive notifications for this project
     */
    protected function getEligibleUsers(DataProyek $project): \Illuminate\Support\Collection
    {
        // Get all users
        $users = User::all();
        
        return $users->filter(function ($user) use ($project) {
            // Super Admin gets all notifications
            if ($user->hasRole('Super Admin')) {
                return true;
            }

            // Project Manager only gets notifications for their bidang jasa
            if ($user->hasRole('Project Manager')) {
                return $user->hasAccessToBidangJasa($project->id_bidjasa);
            }

            return false;
        });
    }

    /**
     * Send pending email notifications
     */
    protected function sendPendingEmails(): int
    {
        $count = 0;

        $pendingNotifications = KontrakNotification::where('email_sent', false)
            ->with(['user', 'project'])
            ->get()
            ->groupBy('user_id');

        foreach ($pendingNotifications as $userId => $notifications) {
            $user = $notifications->first()->user;
            
            if (!$user || !$user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new KontrakExpiredMail($user, $notifications));
                
                // Mark all notifications as email sent
                foreach ($notifications as $notification) {
                    $notification->markEmailSent();
                }
                
                $count += $notifications->count();
            } catch (\Exception $e) {
                Log::error('Failed to send kontrak notification email', [
                    'user_id' => $userId,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return KontrakNotification::forUser($userId)
            ->unread()
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notification count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return KontrakNotification::forUser($userId)
            ->unread()
            ->count();
    }

    /**
     * Get all notifications for a user (paginated)
     */
    public function getAllNotifications(int $userId, int $perPage = 15)
    {
        return KontrakNotification::forUser($userId)
            ->with('project')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = KontrakNotification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        return KontrakNotification::forUser($userId)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
