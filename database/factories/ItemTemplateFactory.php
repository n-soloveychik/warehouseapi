<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\ItemTemplate::class, function (Faker $faker) {
    return [
        'category_id' => 1,
        'item_num' => $faker->numerify('####.####.####'),
        'image' => 'http://via.placeholder.com/640x360',
        'weight' => $faker->randomFloat(1, 0,100),
        'size' => $faker->numerify('###x###x###'),
        'description' => $faker->realText(50),
    ];
});
