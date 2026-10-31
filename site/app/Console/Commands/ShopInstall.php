<?php

namespace App\Console\Commands;

use App\Shop\Installer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ShopInstall extends Command
{
    protected $signature = 'shop:install {--no-migrate : Skip running the database migrations first}';

    protected $description = "Run the migrations, set up Big Ed's starting data (languages, admin, texts, products) and copy the old site's orders. Safe to run on every deploy.";

    public function handle(Installer $installer): int
    {
        try {
            // On Postgres the site's tables live in their own schema (config/database.php).
            if (DB::connection()->getDriverName() === 'pgsql') {
                $schema = (string) config('database.connections.pgsql.search_path');
                DB::statement('create schema if not exists '.DB::connection()->getQueryGrammar()->wrap($schema));
            }
            // One command for Railway's pre-deploy step: no `&&` needed there.
            if (! $this->option('no-migrate')) {
                $this->call('migrate', ['--force' => true]);
            }
            $installer->run();
        } catch (Throwable $e) {
            // Say plainly what went wrong, so it is readable in Railway's deploy logs.
            $this->error('shop:install failed: '.$e->getMessage());

            return self::FAILURE;
        }
        foreach ($installer->log ?: ['Everything is already set up.'] as $line) {
            $this->info($line);
        }

        return self::SUCCESS;
    }
}
