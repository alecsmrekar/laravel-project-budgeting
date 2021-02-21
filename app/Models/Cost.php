<?php

namespace App\Models;

use App\Http\Controllers\CostController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model {
    use HasFactory;

    public static function get_cost_array($pid=false, $cid=false) {
        $data = [];
        $query = self::query();
        if ($pid) {
            $query->where('project_id', $pid);
        }
        if ($cid) {
            $query->where('id', $cid);
        }

        foreach ($query->get() as $item) {
            $add = $item->getAttributes();
            array_push($data, $add);
        }
        return $data;
    }

    public static function generate_service_title($item){
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
        return $service_full_name;
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
        return $cost->getAttributes();
    }

    public static function create($data, $get_actuals = TRUE) {
        $cost = self::write($data, FALSE, $get_actuals);
        return CostController::read_one($cost['id'], $get_actuals);
    }

    public static function update_cost($data, $get_actuals = TRUE) {
        $cost = self::find($data['id']);
        $cost = self::write($data, $cost, $get_actuals);
        return CostController::read_one($cost['id'], $get_actuals);
    }

    public static function delete_cost($id) {
        $item = self::find($id);
        $item->delete();
    }

}
