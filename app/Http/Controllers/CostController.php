<?php


namespace App\Http\Controllers;

use App\Engines;
use App\Models\Cost;


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
            $actuals = Engines\CashflowEngine::get_actuals($project_id, $cid);

            foreach ($data as $key => $item) {
                $cid = $item['id'];
                $data[$key] = array_merge($data[$key], $actuals[$cid]);
            }
        }
        return $data;
    }

}
