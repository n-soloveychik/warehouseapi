<?php

use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            'Ожидает доставку',
            'Частично в наличии',
            'Притензии',
            'В наличии',
            'Работы по рекламации',
            'Архивирован',
        ];
        foreach ($statuses as $status)
            \App\Models\OrderStatus::create([
                'status' => $status
            ]);
    }
}
