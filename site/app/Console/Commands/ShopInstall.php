<?php

namespace App\Console\Commands;

use App\Shop\Installer;
use Illuminate\Console\Command;

class ShopInstall extends Command
{
    protected $signature = 'shop:install';

    protected $description = "Set up Big Ed's starting data (languages, admin, texts, products) and copy the old site's orders. Safe to run on every deploy.";

    public function handle(Installer $installer): int
    {
        $installer->run();
        foreach ($installer->log ?: ['Everything is already set up.'] as $line) {
            $this->info($line);
        }

        return self::SUCCESS;
    }
}
