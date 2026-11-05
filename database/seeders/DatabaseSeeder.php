<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akaun contoh untuk pembangunan sahaja. TUKAR kata laluan sebelum
        // dipasang di pelayan sebenar.
        $users = [
            ['name' => 'Admin', 'email' => 'admin@jejakrasul.test', 'role' => Role::Admin],
            ['name' => 'Penulis', 'email' => 'penulis@jejakrasul.test', 'role' => Role::Writer],
            ['name' => 'Penyemak', 'email' => 'penyemak@jejakrasul.test', 'role' => Role::Reviewer],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => 'password', 'email_verified_at' => now()],
            );
        }

        $this->call(HijrahArcSeeder::class);
    }
}
