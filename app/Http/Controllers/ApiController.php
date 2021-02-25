<?php

namespace App\Http\Controllers;

use App\Models\CostLink;
use Illuminate\Http\Request;
use App\Models;
use App\Http\Controllers;
use App\Engines;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller {


    public function getAllCosts2(Request $request, $project_id) {
        if (is_numeric($project_id)) {
            $project_id = intval($project_id);
            return CostController::get_costs($project_id);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    public function getCost(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            return CostController::read_one($id);
        }
        return Response::HTTP_BAD_REQUEST;
    }

    // todo write test
    public function getCostTransactions(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            //$links = CostLink::link_cost_to_transactions(false, $id);
            $engine = new Engines\CashflowEngine(false, $id);
            return $engine->getTransactions();
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
            return CostController::get_tree($id);
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
        return Models\Project::create($data);
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

    public function getCashflow(Request $request) {
        return CostController::get_cashflow_page();
    }

    public function getProject(Request $request, $id) {
        if (is_numeric($id)) {
            $id = intval($id);
            return Models\Project::read_one($id, $binary_active = TRUE);
        }
        return Response::HTTP_BAD_REQUEST;
    }

}
