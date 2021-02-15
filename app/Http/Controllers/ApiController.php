<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller {

    public function getCostActuals(Request $request, $cid) {
        if (is_numeric($cid)) {
            $project_id = intval($cid);
            $providers = [Models\Revolut::class];
            $data = [];
            foreach ($providers as $p) {
                $val = $p::get_cost_actuals($cid);
                $data = array_merge($data, $val);
            }
            return $data;
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getAllActuals(Request $request) {
        $providers = [Models\Revolut::class];
        $data = [];
        foreach ($providers as $p) {
            $val = $p::get_all_actuals();
            $data = array_merge($data, $val);
        }
        return $data;
    }

    public function getProjectActuals(Request $request, $project_id) {
        if (is_numeric($project_id)) {
            $project_id = intval($project_id);
            $providers = [Models\Revolut::class];
            $data = [];
            foreach ($providers as $p) {
                $val = $p::get_project_actuals($project_id);
                $data = array_merge($data, $val);
            }
            return $data;
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getAllCosts(Request $request, $project_id) {
        if (is_numeric($project_id)) {
            $project_id = intval($project_id);
            return Models\Cost::read_all($project_id);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getCost(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            return Models\Cost::read_one($id);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getLinks(Request $request, $provider, $id) {
        if (is_numeric($id) and $provider) {
            $id = intval($id);
            return Models\CostLink::get_transaction_links($provider, $id);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function createCost(Request $request) {
        $input = $request->all();
        return Models\Cost::create($input);
    }

    public function createLink(Request $request) {
        $input = $request->all();
        return Models\CostLink::create($input);
    }

    public function deleteCost(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            Models\Cost::delete_cost($id);
            return Response::HTTP_OK;
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function deleteLink(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            Models\CostLink::delete_link($id);
            return Response::HTTP_OK;
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function deleteProject(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            Models\Project::delete_project($id);
            return Response::HTTP_OK;
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getTree(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            return Models\Cost::get_tree($id);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function updateCost(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            $input = $request->all();
            $input['id'] = $id;
            return Models\Cost::update_cost($input);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function updateLink(Request $request, $provider, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            $input = $request->all();
            $input['id'] = $id;
            return Models\CostLink::update_link($input);
        }
        return Response::HTTP_BAD_REQUEST;
    }


    public function getAllProjects(Request $request) {
        return Models\Project::read_all();
    }

    public function createProject(Request $request) {
        $input = $request->all();
        $data = [
            'name' => $input['name'],
            'client' => $input['client'],
            'active' => ($input['active'] == TRUE ? 1 : 0)
        ];
        Models\Project::create($data);
        return Response::HTTP_OK;
    }

    public function updateProject(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            $input = $request->all();
            $data = [
                'id' => $id,
                'name' => $input['name'],
                'client' => $input['client'],
                'active' => ($input['active'] == TRUE ? 1 : 0)
            ];
            return Models\Project::update_project($data);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getAllTransactions(Request $request) {
        return Models\Transaction::read_all();
    }

    public function getHeaders(Request $request) {
        return Models\Transaction::getHeaders();
    }

    public function getProject(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            return Models\Project::read_one($id, $binary_active = TRUE);
        }
        return Response::HTTP_BAD_REQUEST;
    }

}
