<?php

namespace Database\Factories;

use App\Models\Revolut;
use Illuminate\Database\Eloquent\Factories\Factory;

class RevolutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Revolut::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'number' => 100,
            'time' => $this->faker->dateTime(),
            'type' => 'Deposit',
            'amount' => 175,
            'account' => 'main',
            'counterparty' => $this->faker->name,
            'currency' => 'EUR'
        ];
    }
}
