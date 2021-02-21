<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Project;

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_project_db_insert()
    {
        Project::factory()->create();
        $this->assertDatabaseCount('projects', 1);
    }

    public function test_get_project_names_function()
    {
        $project = Project::factory()->create();
        $names = Project::get_project_names();
        $this->assertEquals($project->name, $names[$project->id]);
    }

    public function test_read_all_function()
    {
        Project::factory()->count(10)->create();
        $all = Project::read_all();
        $this->assertEquals(10, count($all));
        $this->assertEquals(10, $all[9]['id']);
    }
}
