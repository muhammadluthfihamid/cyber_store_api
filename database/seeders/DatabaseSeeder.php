<?php

namespace Database\Seeders;

use Database\Seeders\StoreSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StoreSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ProductSeeder::class);
    }
}
