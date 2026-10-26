<?php

namespace Database\Seeders;

use App\Support\DashboardMenuPermissions;
use Illuminate\Database\Seeder;

class DashboardMenuPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $count = DashboardMenuPermissions::sync(true);

        $this->command?->info("Synced {$count} dashboard menu permissions.");
    }
}
