<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function listView(Request $request) {
        return view('project_list');
    }

    public function editorView(Request $request) {
        return view('project_editor');
    }

    public function viewProjectForm(Request $request, $id = False) {
        return view('create_project')->with([
            'project' => ($id ? Project::find($id) : False)
        ]);
    }

    public function formSubmit(Request $request) {
        $this->validate(request(), [
            'name' => 'required|',
            'client' => 'required'
        ]);

        $post_data = $request->input();
        $data = [
            'name' => $post_data['name'],
            'client' => $post_data['client'],
            'active' => (array_key_exists('active', $post_data) ? 1 : 0 )
        ];

        $route = $request->route();
        if ($route->getName() == 'edit_project_submit') {
            $id = $route->parameter('id');
            $project = Project::find($id);
            $project->name = $data['name'];
            $project->client = $data['client'];
            $project->active = $data['active'];
            $project->save();
        } else {
            Project::create($data);
        }
    }
}
