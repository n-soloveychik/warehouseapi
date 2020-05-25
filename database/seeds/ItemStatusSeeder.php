<?php

use Illuminate\Database\Seeder;

class ItemStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            'В наличии',
            'Ожидает доставку',
            'Притензии',
            'Отгружен',
        ];

        foreach ($statuses as $status){
            $statusModel = new \App\Models\ItemStatus();
            $statusModel->status = $status;
            $statusModel->save();
        }
    }
}
