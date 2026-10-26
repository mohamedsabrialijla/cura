<?php

namespace App\Console\Commands;

use App\Support\DashboardMenuPermissions;
use Illuminate\Console\Command;

class SyncDashboardMenuPermissions extends Command
{
    protected $signature = 'permissions:sync-dashboard-menus {--no-assign : Do not assign all menus to existing roles}';

    protected $description = 'Replace permissions with dashboard menu items only';

    public function handle(): int
    {
        $count = DashboardMenuPermissions::sync(! $this->option('no-assign'));

        $this->info("Synced {$count} dashboard menu permissions.");

        return self::SUCCESS;
    }
}
