<?php


namespace App\Http\Controllers;

use App\Engines;
use App\Models\Cost;
use App\Models\CostLink;
use App\Models\Project;


class CostController {

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
        $output = $engine->get_actuals_by_event();
        usort($output, function ($a, $b) {
            $t1 = strtotime($a['date']);
            $t2 = strtotime($b['date']);
            return $t1 - $t2;
        });
        return $output;
    }

    /*
     * Returns a projects cost tree
     * Departments are array keys
     * Items are dictionaries of costs using their id as the key
     */
    public static function get_tree($id) {
        $all = self::get_costs($id, FALSE);
        $links = self::which_costs_are_linked($id);
        $output = [];
        foreach ($all as $item) {
            $add = [];
            $is_linked = (in_array($item['id'], $links) ? '' : ' -> not yet linked');
            $service_full_name = Cost::generate_service_title($item);
            $dep = $item['department'];
            if (array_key_exists($dep, $output) == FALSE) {
                $output[$dep] = [];
            }
            $output[$dep][$item['id']] = $service_full_name . $is_linked;
        }
        return $output;
    }

    public static function which_costs_are_linked($pid) {
        $output = [];
        $links = CostLink::link_cost_to_transactions($pid);
        foreach ($links as $i) {
            array_push($output, $i['cost_id']);
        }
        return $output;
    }

}
