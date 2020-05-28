<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\Item::class, function (Faker $faker) {
    return [
        'status_id' => 1,
        'category_id' => 1,
        'count' => rand(1,6),
        'weight' => $faker->randomFloat(1, 0,100),
        'item_num' => $faker->numerify('####.####.####'),
        'lot' => rand(0, 7),
        'image' => 'http://via.placeholder.com/640x360',
        'size' => $faker->numerify('###x###x###'),
        'description' => $faker->realText(50),
    ];
});
