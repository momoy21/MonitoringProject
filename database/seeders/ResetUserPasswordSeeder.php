<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset Super Admin password
        $superAdmin = User::where('email', 'superadmin@gmail.com')->first();
        if ($superAdmin) {
            $superAdmin->password = Hash::make('p@ssw0rd4j4');
            $superAdmin->save();
            $this->command->info('Super Admin password reset to: p@ssw0rd4j4');
        }

        // Reset Project Manager password
        $pm = User::where('email', 'pm@gmail.com')->first();
        if ($pm) {
            $pm->password = Hash::make('password');
            $pm->save();
            $this->command->info('Project Manager password reset to: password');
        }

        $this->command->info('All user passwords have been reset!');
    }
}
