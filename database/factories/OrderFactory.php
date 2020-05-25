<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\Order::class, function (Faker $faker) {
    return [
        'warehouse_id' => 1,
        'order_num' => $faker->numerify('####'),
        'status_id' => 1
    ];
});
