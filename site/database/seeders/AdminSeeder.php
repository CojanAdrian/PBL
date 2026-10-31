<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Create the first administrator from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
     */
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD in your .env file before seeding.');
        }

        User::updateOrCreate(['email' => $email], [
            'name' => 'Admin',
            'password' => $password,
            'role_id' => Role::where('role', Role::ADMIN)->value('id'),
            'active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
