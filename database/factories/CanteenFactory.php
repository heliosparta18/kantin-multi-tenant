<?php

namespace Database\Factories;

use App\Models\Canteen;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Canteen> */
class CanteenFactory extends Factory
{
    protected $model = Canteen::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'code' => strtoupper(Str::random(6)),
            'name' => $name,
            'status' => 'active',
        ];
    }
}
