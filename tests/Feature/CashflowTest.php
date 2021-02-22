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
            'manual_actuals_date' => $this->faker->date(),
        ]);

        $response = $this->actingAs($this->super_user)
            ->getJson('/api/cashflow/all');

        $response_data = $response->getOriginalContent();
        $this->assertEquals(-99, $response_data[0]['amount']);
    }

    public function test_if_api_call_returns_transaction_actuals() {
        $project = Project::factory()->create();
        $cost = Cost::factory()->create([
            'project_id' => 1,
        ]);
        $transaction = Revolut::factory()->create(['number' => 100, 'amount' => 175]);
        $link = CostLink::factory()->create([
            'transaction_id' => 100,
            'cost_id' => 1,
            'provider' => 'Revolut'
        ]);

        $response = $this->actingAs($this->super_user)
            ->getJson('/api/cashflow/all');

        $response_data = $response->getOriginalContent();
        $this->assertEquals(175, $response_data[0]['amount']);
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
            'amount'
        ];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $response_data[0]);
        }
    }
}
