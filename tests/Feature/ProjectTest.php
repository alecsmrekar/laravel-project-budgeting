<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase {

    use RefreshDatabase;

    private $super_user;

    protected function setUp(): void {
        parent::setUp();
        $this->super_user = UserFactory::new()->create([
            'role' => 'super'
        ]);
    }

    /**
     * Test if creating a project via the API return an object.
     */
    public function test_if_unauth_api_access_is_denied() {
        $post = [
            'name' => 'Project',
            'client' => 'Client',
            'active' => 1
        ];
        $response = $this->postJson('/api/projects/create', $post);
        $response->assertStatus(401);
    }

    /**
     * Test if creating a project via the API return an object.
     */
    public function test_if_creating_project_via_api_returns_object() {
        $post = [
            'name' => 'Project',
            'client' => 'Client',
            'active' => 1
        ];
        $response = $this->actingAs($this->super_user)
            ->postJson('/api/projects/create', $post);
        $response
            ->assertJson([
                'name' => $post['name'],
                'client' => $post['client'],
                'active' => $post['active'],
            ]);
    }

    /**
     * Test if creating a project via the API return an object.
     */
    public function test_if_creating_project_via_api_returns_id() {
        $post = [
            'name' => 'Project',
            'client' => 'Client',
            'active' => 1
        ];
        $response = $this->actingAs($this->super_user)
            ->postJson('/api/projects/create', $post);
        $response->assertJsonStructure([
            'name',
            'client',
            'active',
            'id'
        ]);
    }

    public function test_api_delete_call_removes_db_record() {
        $project = Project::factory()->create();
        $this->assertDatabaseCount('projects', 1);
        $this->actingAs($this->super_user)
            ->postJson('/api/projects/delete/' . $project->id);
        $project = $this->assertDatabaseCount('projects', 0);
    }

    public function test_api_edit_call_updates_db_record() {
        $project = Project::factory()->create();
        $this->assertDatabaseCount('projects', 1);
        $this->actingAs($this->super_user)
            ->postJson('/api/projects/update/' . $project->id,
                [
                    'name' => 'TestName',
                    'client' => 'TestClient',
                    'active' => 0
                ]);
        $project = Project::find($project->id);
        $this->assertEquals($project->name, 'TestName');
        $this->assertEquals($project->client, 'TestClient');
        $this->assertEquals($project->active, 0);
    }
}
