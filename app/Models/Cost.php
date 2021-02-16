<?php

namespace App\Models;

use App\Engines;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model {
    use HasFactory;

    // move to cashflow engine
    public static function read_one($id) {
        $data = self::where('id', $id)->first();
        $providers = [
            'Revolut' => Revolut::class
        ];
        $data = self::obj_to_array($data);
        $data['actuals'] = 0;
        if (is_numeric($data['manual_actuals'])) {
            $data['actuals'] += $data['manual_actuals'];
        }
        foreach ($providers as $p => $pro_class) {
            $val = $pro_class::get_cost_actuals($id);
            if (isset($val[$id])) {
                $data['actuals'] += $val[$id]['amount'];
            }
        }
        $data['diff'] = $data['budget'] - $data['actuals'];
        return $data;
    }

    // Converts the object to an array
    public static function obj_to_array($item) {
        return [
            'project_id' => $item->project_id,
            'department' => $item->department,
            'id' => $item->id,
            'sector' => $item->sector,
            'service' => $item->service,
            'person' => $item->person,
            'company' => $item->company,
            'budget' => $item->budget,
            'tax_rate' => $item->tax_rate,
            'final' => $item->final,
            'comment' => $item->comment,
            'manual_actuals' => $item->manual_actuals,
            'manual_actuals_tag' => $item->manual_actuals_tag,
        ];
    }

    /*
     * Returns a projects cost tree
     * Departments are array keys
     * Items are dictionaries of costs using their id as the key
     */
    public static function get_tree($id) {
        $all = self::read_all($id);
        $output = [];
        foreach ($all as $item) {
            $add = [];
            $dep = $item['department'];
            $sector = $item['sector'];
            $service = $item['service'];
            $person = $item['person'];
            $company = $item['company'];
            $service_full_name = $service;
            if ($sector) {
                $service_full_name = $sector . ': ' . $service_full_name;
            }
            if ($person) {
                $service_full_name = $service_full_name . ': ' . $person;
            }
            if ($company) {
                $service_full_name = $service_full_name . ' (' . $company . ')';
            }
            if (array_key_exists($dep, $output) == FALSE) {
                $output[$dep] = [];
            }
            $output[$dep][$item['id']] = $service_full_name;
        }
        return $output;
    }

    public static function get_project_costs($project_id) {
        $data = [];
        $manual_actuals = [];
        $actuals_field = self::get_actuals_field();
        $query = self::query()
            ->where('project_id', $project_id)->get();
        foreach ($query as $item) {
            $add = self::obj_to_array($item);
            array_push($data, $add);

            $manual_actuals[$add['id']]['manual'] = $add[$actuals_field];
            $manual_actuals[$add['id']]['budget'] = $add['budget'];
        }

        $actuals = Engines\CashflowEngine::get_project_actuals($project_id, $manual_actuals);
        foreach ($data as $key => $item) {
            $cid = $item['id'];
            $data[$key]['actuals'] = 0;
            $data[$key]['diff'] = $item['budget'];
            if (array_key_exists($cid, $actuals)) {
                $data[$key]['actuals'] = $actuals[$cid]['actuals'];
                $data[$key]['diff'] = $actuals[$cid]['diff'];
            }
        }
        return $data;
    }

    public static function get_actuals_field() {
        return 'manual_actuals';
    }

// move to cashflow engine
    public
    static function read_all($project_id): array {
        $data = [];
        $query = self::query()
            ->where('project_id', $project_id)->get();
        $providers = [
            'Revolut' => Revolut::class
        ];
        $actuals = [];
        foreach ($providers as $p => $pro_class) {
            $val = $pro_class::get_project_actuals($project_id);
            $actuals[$p] = $val;
        }
        foreach ($query as $item) {
            $add = self::obj_to_array($item);
            $add['actuals'] = 0;
            if (is_numeric($add['manual_actuals'])) {
                $add['actuals'] += $add['manual_actuals'];
            }
            foreach ($providers as $p => $pro_class) {
                if (isset($actuals[$p][$add['id']]['amount'])) {
                    $add['actuals'] += $actuals[$p][$add['id']]['amount'];
                }
            }
            $add['diff'] = $add['budget'] - $add['actuals'];
            array_push($data, $add);
        }

        return $data;
    }

// Update a cost
    public
    static function update_cost($data) {
        $cost = self::find($data['id']);
        return self::write($data, $cost);
    }

// Delete a cost
    public
    static function delete_cost($id) {
        $item = self::find($id);
        $item->delete();
    }

// Create a cost
    public
    static function create($data) {
        return self::write($data);
    }

// Write to the DB. Either new or update existing.
    public
    static function write(array $data, $cost = FALSE) {
        if ($cost == FALSE) {
            $cost = new self();
        }
        $cost->project_id = $data['project_id'];
        $cost->department = $data['department'];
        $cost->sector = $data['sector'];
        $cost->service = $data['service'];
        $cost->person = $data['person'];
        $cost->company = $data['company'];
        $cost->budget = $data['budget'];
        $cost->tax_rate = $data['tax_rate'];
        $cost->final = $data['final'];
        $cost->comment = $data['comment'];
        $cost->manual_actuals = $data['manual_actuals'];
        $cost->manual_actuals_tag = $data['manual_actuals_tag'];
        $cost->save();

        return self::read_one($cost->id);
    }
}
