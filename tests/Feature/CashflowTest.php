<?php

namespace Tests\Feature;

use App\Models\Cost;
use App\Models\CostLink;
use App\Models\Project;
use App\Models\Revolut;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CashflowTest extends TestCase {
    use WithFaker;
    use RefreshDatabase;

    private $super_user;

    protected function setUp(): void {
        parent::setUp();
        $this->super_user = UserFactory::new()->create([
            'role' => 'super'
        ]);
    }

    public function test_if_api_call_returns_cost_manual_actuals() {
        $project = Project::factory()->create();
        $cost = Cost::factory()->create([
            'project_id' => 1,
            'budget' => 100,
            'manual_actuals' => 99,
            'tax_rate' => 0,
            'manual_actuals_date' => $this->faker->date(),
        ]);

        $response = $this->actingAs($this->super_user)
            ->getJson('/api/cashflow/all');

        $response_data = $response->getOriginalContent();
        $net = -99;
        $tax = 0;
        $this->assertEquals(-99, $response_data[0]['actuals']);
        $this->assertEquals($cost['manual_actuals_date'], $response_data[0]['date']);
        $this->assertEquals($net, $response_data[0]['actuals_net']);
        $this->assertEquals($tax, $response_data[0]['tax_part']);
    }

    public function test_if_api_call_returns_transaction_actuals() {
        $project = Project::factory()->create();
        $cost = Cost::factory()->create([
            'project_id' => 1,
            'tax_rate' => 0.2,
        ]);
        $transaction = Revolut::factory()->create(['number' => 100, 'amount' => -175, 'time' =>$this->faker->date()]);
        $link = CostLink::factory()->create([
            'transaction_id' => 100,
            'cost_id' => 1,
            'provider' => 'Revolut'
        ]);

        $response = $this->actingAs($this->super_user)
            ->getJson('/api/cashflow/all');

        $response_data = $response->getOriginalContent();
        $net = round(-175 / (1.2),2);
        $tax = -175 - $net;
        $this->assertEquals(-175, $response_data[0]['actuals']);
        $this->assertEquals($tax, $response_data[0]['tax_part']);
        $this->assertEquals($net, $response_data[0]['actuals_net']);
        $this->assertEquals($transaction['time'], $response_data[0]['date']);
    }

    public function test_if_api_call_returns_entire_array() {
        $project = Project::factory()->create();
        $cost = Cost::factory()->create([
            'project_id' => 1,
            'budget' => 100,
            'manual_actuals' => 99,
            'manual_actuals_date' => $this->faker->date(),
        ]);

        $response = $this->actingAs($this->super_user)
            ->getJson('/api/cashflow/all');

        $response_data = $response->getOriginalContent();
        $keys = [
            'project',
            'project_id',
            'department',
            'sector',
            'cost',
            'cost_id',
            'final',
            'date',
            'actuals'
        ];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $response_data[0]);
        }
    }
}
