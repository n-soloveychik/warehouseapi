<?php

use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Warehouse::create([
            'address' => 'Saint Petersburg',
            'photo' => 'http://via.placeholder.com/640x360'
        ]);
    }
}
