<?php


namespace App\Http\Controllers;

use App\Engines;
use App\Models\Cost;
use App\Models\Project;


class CostController {

    // Create a cost
    public static function create($data, $get_actuals = TRUE) {
        $cost = Cost::write($data, FALSE, $get_actuals);
        return self::read_one($cost['id'], $get_actuals);
    }


    // Update a cost
    public static function update_cost($data, $get_actuals = TRUE) {
        $cost = Cost::find($data['id']);
        $cost = Cost::write($data, $cost, $get_actuals);
        return self::read_one($cost['id'], $get_actuals);
    }

// Delete a cost
    public static function delete_cost($id) {
        $item = Cost::find($id);
        $item->delete();
    }

    // Returns a single cost
    public static function read_one($id, $get_actuals = FALSE) {
        $data = Cost::find($id);
        $all = self::get_costs($data->project_id, $get_actuals, $id);
        if (isset($all[0])) {
            return $all[0];
        }
        return [];
    }

    public static function get_costs($project_id, $add_actuals = TRUE, $cid=false) {
        $data = Cost::get_cost_array($project_id, $cid);

        // Use the cashflow engine to calculate actuals
        // Pass the costs manuals field as an argument
        if ($add_actuals) {
            $engine = new Engines\CashflowEngine($project_id, $cid);
            $actuals = $engine->get_actuals();

            foreach ($data as $key => $item) {
                $cid = $item['id'];
                $data[$key] = array_merge($data[$key], $actuals[$cid]);
            }
        }
        return $data;
    }

    public static function get_cashflow_page(){
        $engine = new Engines\CashflowEngine();
        $engine->receiveAllCostData();
        $actuals = $engine->get_actuals();
        $project_names = Project::get_project_names();
        $output = [];
        foreach ($actuals as $cost) {
            $item = [
                'project' => $project_names[$cost['project_id']],
                'project_id' => $cost['project_id'],
                'department' => $cost['department'],
                'sector' => $cost['sector'],
                'cost' => Cost::generate_service_title($cost),
                'cost_id' => $cost['id'],
                'final' => $cost['final'],
            ];

            // Check manuals first
            $manual = $cost['manual_actuals'];
            $manual_date = $cost['manual_actuals_date'];
            if ($manual_date) {
                $insert = $item;
                $insert['date'] = $manual_date;
                $insert['amount'] = $manual;
                array_push($output, $insert);
            }

            // Loop transactions second
            foreach ($cost['transactions_data'] as $tr) {
                $insert = $item;
                $insert['date'] = $tr['date'];
                $insert['amount'] = $tr['amount'];
                array_push($output, $insert);
            }
        }
        usort($output, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });
        return $output;
    }

}
