<?php

namespace Database\Factories;

use App\Models\Cost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\WithFaker;

class CostFactory extends Factory
{
    use WithFaker;
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cost::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'department' => $this->faker->word,
            'sector' => $this->faker->word,
            'service' => $this->faker->jobTitle,
            'person' => $this->faker->name,
            'company' => $this->faker->company,
            'final' => 0,
            'budget' => 100,
            'tax_rate' => 0,
        ];
    }
}
