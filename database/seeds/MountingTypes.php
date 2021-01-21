<?php

use Illuminate\Database\Seeder;

class MountingTypes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'Синтетическое УПП',
            'Анкерный',
            'Свободностоящий',
            'Насыпное УПП',
            'Спец. монтаж',
        ];

        foreach ($types as $type){
            \App\Models\MountingType::create([
                'type' => $type
            ]);
        }

    }
}
