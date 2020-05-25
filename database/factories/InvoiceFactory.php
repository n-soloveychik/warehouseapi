<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\Invoice::class, function (Faker $faker) {
    return [
        'invoice_code' => $faker->numerify('####.0000.0000')
    ];
});
