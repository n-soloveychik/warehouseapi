<?php

use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            'Без категории',
            'Фанера',
            'Метал',
            'Стойки, балки',
            'Деревянные и сборные элементы',
            'Сетки, канаты',
            'Горки',
            'Качели, качалки',
            'Метизы',
            'Столярные элементы',
        ];

        foreach ($categories as $category){
            $categoryModel = new \App\Models\ItemCategory();
            $categoryModel->category_name = $category;
            $categoryModel->save();
        }
    }
}
