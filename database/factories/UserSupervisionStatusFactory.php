<?php

namespace Database\Factories;

use App\UserSupervisionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSupervisionStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserSupervisionStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->title,
            'is_archive' => $this->faker->boolean,
            'color' => $this->faker->hexColor ,
        ];
    }
}
