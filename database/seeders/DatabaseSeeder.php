<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProvincesCitiesSeeder::class,
            SpesifikasiRABSeeder::class,
        ]);

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Konsumen
            'view konsumen',
            'create konsumen',
            'edit konsumen',
            'delete konsumen',

            // Bidang Jasa
            'view bidang jasa',
            'create bidang jasa',
            'edit bidang jasa',
            'delete bidang jasa',

            // Master Manager
            'view master manager',
            'create master manager',
            'edit master manager',
            'delete master manager',

            // Kondisi Proyek
            'view kondisi proyek',
            'create kondisi proyek',
            'edit kondisi proyek',
            'delete kondisi proyek',

            // Spesifikasi RAB
            'view spesifikasi rab',
            'create spesifikasi rab',
            'edit spesifikasi rab',
            'delete spesifikasi rab',

            // Summary RAB
            'view summary rab',
            'create summary rab',
            'edit summary rab',
            'delete summary rab',

            // Data Peluang
            'view data peluang',
            'create data peluang',
            'edit data peluang',
            'delete data peluang',

            // Data Proyek
            'view data proyek',
            'create data proyek',
            'edit data proyek',
            'delete data proyek',

            // Upload RAB
            'view upload rab',
            'upload rab',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $projectManagerRole = Role::create(['name' => 'Project Manager']);

        // Assign all permissions to Super Admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to Project Manager
        $pmPermissions = [
            'view konsumen',
            'view data peluang',
            'create data peluang',
            'edit data peluang',
            'delete data peluang',
            'view data proyek',
            'create data proyek',
            'edit data proyek',
            'delete data proyek',
            'view upload rab',
            'upload rab',
        ];
        $projectManagerRole->givePermissionTo($pmPermissions);

        // Create Super Admin user
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('p@ssw0rd4j4'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('Super Admin');

        // Create Project Manager user
        $projectManager = User::create([
            'name' => 'Project Manager',
            'email' => 'pm@gmail.com',
            'password' => bcrypt('p@ssw0rd4j4'),
            'email_verified_at' => now(),
        ]);
        $projectManager->assignRole('Project Manager');
    }
}
