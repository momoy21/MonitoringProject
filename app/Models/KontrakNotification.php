<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class KontrakNotification extends Model
{
    protected $table = 'kontrak_notifications';

    protected $fillable = [
        'id_project',
        'user_id',
        'type',
        'title',
        'message',
        'no_kontrak',
        'finish_kontrak',
        'is_read',
        'email_sent',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
        'finish_kontrak' => 'date',
        'read_at' => 'datetime',
    ];

    /**
     * Konstanta untuk tipe notifikasi
     */
    const TYPE_EXPIRED = 'expired';      // Kontrak sudah habis
    const TYPE_EXPIRING = 'expiring';    // Akan habis dalam 30 hari

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke DataProyek
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(DataProyek::class, 'id_project', 'id_project');
    }

    /**
     * Scope untuk notifikasi yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope untuk notifikasi milik user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk notifikasi tipe tertentu
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark email as sent
     */
    public function markEmailSent(): void
    {
        $this->update(['email_sent' => true]);
    }

    /**
     * Get formatted finish date
     */
    public function getFormattedFinishDateAttribute(): string
    {
        if (!$this->finish_kontrak) {
            return '-';
        }
        return Carbon::parse($this->finish_kontrak)->format('d/m/Y');
    }

    /**
     * Get relative time (contoh: "2 hari lalu")
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get badge class based on type
     */
    public function getBadgeClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_EXPIRED => 'bg-danger',
            self::TYPE_EXPIRING => 'bg-warning',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status text based on type
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->type) {
            self::TYPE_EXPIRED => 'KONTRAK HABIS',
            self::TYPE_EXPIRING => 'AKAN BERAKHIR',
            default => '-',
        };
    }

    /**
     * Get icon class based on type
     */
    public function getIconClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_EXPIRED => 'bx bx-error-circle text-danger',
            self::TYPE_EXPIRING => 'bx bx-time text-warning',
            default => 'bx bx-bell text-secondary',
        };
    }
}
