<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "category_id" => $this->faker->randomElement(Category::all()->range(1, 10)),
            "name" => $this->faker->name(),
            "description" => $this->faker->text(),
        ];
    }
}
