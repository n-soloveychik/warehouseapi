<?php

use Illuminate\Database\Seeder;

class InvoiceStatusSeeder extends Seeder
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
            \App\Models\InvoiceStatus::create([
                'status' => $status
            ]);
    }
}
