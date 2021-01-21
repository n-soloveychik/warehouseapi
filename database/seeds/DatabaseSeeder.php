<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ItemCategorySeeder::class,
            ItemStatusSeeder::class,
            WarehouseSeeder::class,
            OrderStatusSeeder::class,
            InvoiceStatusSeeder::class,
            MountingTypes::class,
            //OrderSeeder::class,
        ]);
    }
}
