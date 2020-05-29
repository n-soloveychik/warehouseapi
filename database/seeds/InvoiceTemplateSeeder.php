<?php

use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\InvoiceTemplate::class, 1000)->create()->each(function (\App\Models\InvoiceTemplate $invoiceTemplate) {
            $categoryIds = [];
            for ($i = 0; $i < 3; $i++) {
                $categoryIds[$i] = rand(1, 10);
            }
            factory(\App\Models\ItemTemplate::class, rand(10,20))->make()->each(function (\App\Models\ItemTemplate $itemTemplate) use ($invoiceTemplate, $categoryIds){
                $itemTemplate->category_id = $categoryIds[rand(0,2)];
                $itemTemplate->save();

                $invoiceTemplate->items()->attach($itemTemplate->item_id, [
                    'count' => rand(1,10),
                    'lot' => rand(1,5)
                ]);
            });
        });
    }
}
