<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Konsumen extends Model
{
    use HasFactory;

    protected $table = 'konsumen';
    protected $primaryKey = 'id_konsumen';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_konsumen',
        'konsumen',
        'provinsi_id',
        'kota_id',
        'alamat1',
        'alamat2',
        'kode_pos',
        'telp_kantor',
        'fax',
        'email',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_konsumen)) {
                $model->id_konsumen = static::generateIdKonsumen();
            }

            if (empty($model->status)) {
                $model->status = 'A';
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'id_konsumen';
    }

    public static function generateIdKonsumen()
    {
        $lastKonsumen = static::orderByRaw('CAST(SUBSTRING(id_konsumen, 2) AS UNSIGNED) DESC')->first();

        if (!$lastKonsumen) {
            return 'K00001';
        }

        $lastNumber = (int) substr($lastKonsumen->id_konsumen, 1);
        $newNumber = $lastNumber + 1;

        return 'K' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function scopeSearch(Builder $query, $search)
    {
        if (!empty($search)) {
            return $query->where('konsumen', 'LIKE', "%{$search}%")
                        ->orWhere('id_konsumen', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
        }

        return $query;
    }

    // Scope untuk hanya menampilkan konsumen aktif
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'A');
    }

    // Scope untuk hanya menampilkan konsumen non-aktif
    public function scopeInactive(Builder $query)
    {
        return $query->where('status', 'N');
    }

    // Accessor untuk format tampilan
    public function getAlamatLengkapAttribute()
    {
        $alamat = collect([
            $this->alamat1,
            $this->alamat2,
            $this->kota->nama ?? null,
            $this->provinsi->nama ?? null,
            $this->kode_pos
        ])->filter()->implode(', ');

        return $alamat ?: '-';
    }

    public function getKontakAttribute()
    {
        $kontak = [];
        if ($this->telp_kantor) $kontak[] = 'Telp: ' . $this->telp_kantor;
        if ($this->fax) $kontak[] = 'Fax: ' . $this->fax;
        if ($this->email) $kontak[] = 'Email: ' . $this->email;

        return !empty($kontak) ? implode(', ', $kontak) : '-';
    }
}
