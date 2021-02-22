<?php

namespace Database\Factories;

use App\Models\CostLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class CostLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CostLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'cost_id' => 1,
            'transaction_id' => 100,
            'amount' => 100,
            'currency' => 'EUR'
        ];
    }
}
