<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LangSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            PageSeed::class,
            ImgSeeder::class,
            KeySeeder::class,
        ]);
    }
}
