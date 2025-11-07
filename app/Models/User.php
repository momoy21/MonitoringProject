<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method bool hasRole(string|array|\Spatie\Permission\Contracts\Role $roles, string|null $guard = null)
 * @method bool hasAnyRole(string|array|\Spatie\Permission\Contracts\Role ...$roles)
 * @method \Illuminate\Database\Eloquent\Relations\MorphToMany roles()
 * @method $this assignRole(...$roles)
 * @method $this syncRoles(...$roles)
 * @method $this removeRole($role)
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bidang_jasa_ids',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the allowed bidang jasa IDs for this user.
     * Returns array of IDs or empty array if all allowed.
     */
    public function getAllowedBidangJasaIds(): array
    {
        if (empty($this->bidang_jasa_ids)) {
            return [];
        }

        return json_decode($this->bidang_jasa_ids, true) ?? [];
    }

    /**
     * Check if user has access to specific bidang jasa.
     */
    public function hasAccessToBidangJasa($bidangJasaId): bool
    {
        // Super Admin has access to everything
        if ($this->hasRole('Super Admin')) {
            return true;
        }

        $allowedIds = $this->getAllowedBidangJasaIds();

        // If no specific IDs set, user has access to all
        if (empty($allowedIds)) {
            return true;
        }

        return in_array($bidangJasaId, $allowedIds);
    }

    /**
     * Get filtered bidang jasa query based on user's access.
     */
    public function filterBidangJasaQuery($query)
    {
        // Super Admin can see everything
        if ($this->hasRole('Super Admin')) {
            return $query;
        }

        $allowedIds = $this->getAllowedBidangJasaIds();

        // If no specific IDs set, show all
        if (empty($allowedIds)) {
            return $query;
        }

        return $query->whereIn('id_bidjasa', $allowedIds);
    }
}
