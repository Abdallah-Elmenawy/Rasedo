<?php

namespace Database\Seeders;

use Database\Seeders\all_users\AccessLayerSeeder;
use Database\Seeders\all_users\AccountingLayerSeeder;
use Database\Seeders\all_users\CatalogLayerSeeder;
use Database\Seeders\all_users\PartiesLayerSeeder;
use Database\Seeders\all_users\SettingsLayerSeeder;
use Database\Seeders\all_users\TreasuryLayerSeeder;
use Illuminate\Database\Seeder;

class RasedoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccessLayerSeeder::class,
            AccountingLayerSeeder::class,
            TreasuryLayerSeeder::class,
            SettingsLayerSeeder::class,
            CatalogLayerSeeder::class,
            PartiesLayerSeeder::class,
        ]);
    }
}
