<?php

namespace App\Models;

use App\Engines;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model {
    use HasFactory;


    public static function get_cost_array($pid, $cid=false) {
        $data = [];
        $query = self::query()->where('project_id', $pid);
        if ($cid) {
            $query->where('id', $cid);
        }

        foreach ($query->get() as $item) {
            $add = self::obj_to_array($item);
            array_push($data, $add);
        }
        return $data;
    }

    // Converts the object to an array
    public static function obj_to_array($item) {
        return (array) $item->attributes;
    }

    /*
     * Returns a projects cost tree
     * Departments are array keys
     * Items are dictionaries of costs using their id as the key
     */
    public static function get_tree($id) {
        $all = self::get_costs($id, FALSE);
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

    public static function extract_costs_actuals_info($cost_array) {
        $actuals_field = self::get_actuals_field();
        return [
            'manual' => $cost_array[$actuals_field],
            'budget' => $cost_array['budget']
        ];
    }

    // Returns the name of the field containing cost manual actuals
    public static function get_actuals_field() {
        return 'manual_actuals';
    }

// Write to the DB. Either new or update existing.
    public static function write(array $data, $cost = FALSE) {
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
        $cost->manual_actuals_date = $data['manual_actuals_date'];
        $cost->save();
        return self::obj_to_array($cost);
    }
}
