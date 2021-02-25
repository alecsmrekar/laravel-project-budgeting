<?php

namespace Tests\Unit;

use App\Models\CostLink;
use App\Models\Project;
use App\Models\Revolut;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Cost;

class CostTest extends TestCase {
    use WithFaker;
    use RefreshDatabase;

    public function test_cost_db_insert() {
        $project_id = Project::factory()->create()->id;
        Cost::factory()->create(['project_id' => $project_id]);
        $this->assertDatabaseCount('costs', 1);
    }

    public function test_get_cost_array_function() {
        Project::factory()->count(2)->create();
        Cost::factory()->create(['project_id' => 1]);
        $cost = Cost::factory()->create(['project_id' => 2]);
        $array = Cost::get_cost_array(2);
        $this->assertEquals(1, count($array));
        $this->assertEquals(2, $array[0]['id']);
        $array = Cost::get_cost_array(FALSE, 2);
        $this->assertEquals(1, count($array));
        $this->assertEquals(2, $array[0]['id']);
    }

    public function test_service_name_generation() {
        Project::factory()->create();
        $cost = Cost::factory()->create(['project_id' => 1]);
        $cost = $cost->getAttributes();
        $name = Cost::generate_service_title($cost);
        $sector = $cost['sector'];
        $this->assertStringStartsWith($sector, $name);
        $person = $cost['person'];
        $this->assertStringContainsString(': ' . $person, $name);
        $company = $cost['company'];
        $this->assertStringEndsWith(' (' . $company . ')', $name);
    }

    public function test_cost_deleting_also_deletes_link() {
        Project::factory()->create();
        $cost = Cost::factory()->create(['project_id' => 1]);
        $transaction = Revolut::factory()->create(['number' => 101, 'amount' => 156, 'time' =>$this->faker->date()]);
        $link = CostLink::factory()->create([
            'transaction_id' => 101,
            'cost_id' => 1,
            'provider' => 'Revolut',
            'link_tag' => 'alfa'
        ]);
        $this->assertDatabaseCount('costs', 1);
        $this->assertDatabaseCount('cost_links', 1);
        Cost::delete_cost(1);
        $this->assertDatabaseCount('costs', 0);
        $this->assertDatabaseCount('cost_links', 0);
    }
}
