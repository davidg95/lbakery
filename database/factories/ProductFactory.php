<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(App\Product::class, function (Faker $faker) {
    return [
        'id' => $faker->ean13(),
        'name' => $faker->text(50),
        'description' => $faker->text(100),
        'price' => $faker->randomNumber(2),
        'sales' => $faker->randomNumber(3)
    ];
});
