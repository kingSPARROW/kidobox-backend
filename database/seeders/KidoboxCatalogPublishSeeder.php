<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KidoboxCatalogPublishSeeder extends Seeder
{
    public function run(): void
    {
        $this->callWith(KidoboxCatalogSeeder::class, ['publish' => true]);
    }
}
