<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

trait TestHelpers
{
    protected function createSuperAdminUser(): User
    {
        $user = User::factory()->create();
        
        $role = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        
        $user->assignRole($role);
        
        return $user;
    }

    protected function createProjectManagerUser(array $bidangJasaIds = []): User
    {
        $user = User::factory()->create([
            'bidang_jasa_ids' => !empty($bidangJasaIds) ? json_encode($bidangJasaIds) : null,
        ]);
        
        $role = Role::firstOrCreate(['name' => 'Project Manager', 'guard_name' => 'web']);
        
        $user->assignRole($role);
        
        return $user;
    }

    protected function createRegularUser(): User
    {
        return User::factory()->create();
    }

    protected function actingAsSuperAdmin(): User
    {
        $user = $this->createSuperAdminUser();
        $this->actingAs($user);
        
        return $user;
    }

    protected function actingAsProjectManager(array $bidangJasaIds = []): User
    {
        $user = $this->createProjectManagerUser($bidangJasaIds);
        $this->actingAs($user);
        
        return $user;
    }

    protected function logoutCurrentUser()
    {
        $this->app['auth']->forgetGuards();
    }

    protected function grantAllPermissions(User $user): void
    {
        $permissions = Permission::all();
        $user->givePermissionTo($permissions);
    }

    protected function createBidangJasaForUser(User $user, int $count = 3): array
    {
        $bidangJasaIds = [];
        
        for ($i = 1; $i <= $count; $i++) {
            $bidangJasa = \App\Models\BidangJasa::factory()->create([
                'id_bidjasa' => str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
            $bidangJasaIds[] = $bidangJasa->id_bidjasa;
        }
        
        $user->update(['bidang_jasa_ids' => json_encode($bidangJasaIds)]);
        
        return $bidangJasaIds;
    }
}
