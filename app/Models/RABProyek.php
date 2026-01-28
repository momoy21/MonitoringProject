<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RABProyek extends Model
{
    use HasFactory;

    protected $table = 'rab_proyek';
    protected $primaryKey = 'nopengajuan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nopengajuan',
        'dokumen_io',
        'cost_center',
        'nama_project',
        'id_konsumen',
        'id_bidjasa',
        'pm',
        'divisi',
        'jenis_proyek',
        'nilai_proyek',
        'tgl_input',
        'keterangan',
        'progress',
        'hasil_pleno',
        'catatan',
        'margin_rkap',
        'margin_pleno',
        'rab_upload',
        'file_upload',
        'peta_risk_upload',
        'hasil_upload',
    ];

    protected $casts = [
        'nilai_proyek' => 'decimal:2',
        'margin_rkap' => 'decimal:2',
        'margin_pleno' => 'decimal:2',
        'tgl_input' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate nopengajuan
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nopengajuan)) {
                $model->nopengajuan = self::generateNoPengajuan();
            }

            if (empty($model->tgl_input)) {
                $model->tgl_input = Carbon::today();
            }
        });
    }

    /**
     * Generate nomor pengajuan otomatis
     * Format: YYYYMMNN (4 digit tahun + 2 digit bulan + 2 digit nomor urut)
     * Nomor urut reset ke 01 jika bulan berbeda
     */
    public static function generateNoPengajuan()
    {
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $prefix = $year . $month;

        // Get last record for current month
        $lastRecord = self::where('nopengajuan', 'like', $prefix . '%')
            ->orderBy('nopengajuan', 'desc')
            ->first();

        if (!$lastRecord) {
            return $prefix . '01';
        }

        // Extract the sequence number (last 2 digits)
        $lastSequence = (int) substr($lastRecord->nopengajuan, 6, 2);
        $newSequence = $lastSequence + 1;

        return $prefix . str_pad($newSequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'nopengajuan';
    }

    /**
     * Relasi ke Konsumen
     */
    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'id_konsumen', 'id_konsumen');
    }

    /**
     * Relasi ke BidangJasa
     */
    public function bidangJasa()
    {
        return $this->belongsTo(BidangJasa::class, 'id_bidjasa', 'id_bidjasa');
    }

    /**
     * Relasi ke MasterDivisi
     */
    public function masterDivisi()
    {
        return $this->belongsTo(MasterDivisi::class, 'divisi', 'kode_divisi');
    }

    /**
     * Relasi ke JenisProyek
     */
    public function jenisProyek()
    {
        return $this->belongsTo(JenisProyek::class, 'jenis_proyek', 'kode_jenis');
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nopengajuan', 'like', "%{$search}%")
              ->orWhere('cost_center', 'like', "%{$search}%")
              ->orWhere('nama_project', 'like', "%{$search}%")
              ->orWhere('dokumen_io', 'like', "%{$search}%")
              ->orWhereHas('konsumen', function($konsumenQuery) use ($search) {
                  $konsumenQuery->where('konsumen', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get keterangan text
     */
    public function getKeteranganTextAttribute()
    {
        $keterangan = [
            'P' => 'Pleno',
            'T' => 'Tidak Pleno',
            'R' => 'Revisi RAB',
        ];

        return $keterangan[$this->keterangan] ?? '-';
    }

    /**
     * Get progress text
     */
    public function getProgressTextAttribute()
    {
        $progress = [
            '01' => 'Dokumen belum diterima',
            '02' => 'Proses tanda tangan BOD',
            '03' => 'Revisi RAB',
            '04' => 'Done',
        ];

        return $progress[$this->progress] ?? '-';
    }

    /**
     * Get hasil pleno text
     */
    public function getHasilPlenoTextAttribute()
    {
        $hasilPleno = [
            'TT' => 'Tidak Tercapai RKAP',
            'TR' => 'Tercapai RKAP',
        ];

        return $hasilPleno[$this->hasil_pleno] ?? '-';
    }

    /**
     * Get progress badge
     */
    public function getProgressBadgeAttribute()
    {
        $badges = [
            '01' => '<span class="badge bg-warning">Dokumen belum diterima</span>',
            '02' => '<span class="badge bg-info">Proses TTD BOD</span>',
            '03' => '<span class="badge bg-primary">Revisi RAB</span>',
            '04' => '<span class="badge bg-success">Done</span>',
        ];

        return $badges[$this->progress] ?? '<span class="badge bg-secondary">-</span>';
    }

    /**
     * Get hasil pleno badge
     */
    public function getHasilPlenoBadgeAttribute()
    {
        $badges = [
            'TT' => '<span class="badge bg-danger">Tidak Tercapai RKAP</span>',
            'TR' => '<span class="badge bg-success">Tercapai RKAP</span>',
        ];

        return $badges[$this->hasil_pleno] ?? '<span class="badge bg-secondary">-</span>';
    }

    /**
     * Format nilai proyek
     */
    public function getNilaiProyekFormattedAttribute()
    {
        if ($this->nilai_proyek && $this->nilai_proyek > 0) {
            return 'Rp ' . number_format((float) $this->nilai_proyek, 0, ',', '.');
        }
        return '-';
    }

    /**
     * Format tanggal input
     */
    public function getTglInputFormattedAttribute()
    {
        return $this->tgl_input ? Carbon::parse($this->tgl_input)->format('d/m/Y') : '-';
    }
}
