<?php

namespace Database\Seeders;

use App\Shop\Installer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Big Ed's starting data. Same as `php artisan shop:install`. */
    public function run(): void
    {
        $installer = app(Installer::class);
        $installer->run();
        foreach ($installer->log as $line) {
            $this->command?->info($line);
        }
    }
}
