<?php

use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\Order::class, 5)->make()->each(function (\App\Models\Order $order) {
            $order->save();
            $categoryIds = [];
            for ($i = 0; $i < 3; $i++) {
                $categoryIds[$i] = rand(1, 10);
            }

            factory(\App\Models\Invoice::class, rand(4, 13))->make()->each(function (\App\Models\Invoice $invoice) use ($order, $categoryIds) {
                $invoice->order_id = $order->order_id;
                $invoice->save();

                factory(\App\Models\Item::class, rand(5, 25))->make()->each(function (\App\Models\Item $item) use ($invoice, $categoryIds) {
                    $item->invoice_id = $invoice->invoice_id;
                    $item->category_id = $categoryIds[rand(0, 2)];
                    $item->save();
                });


            });
        });
    }
}
