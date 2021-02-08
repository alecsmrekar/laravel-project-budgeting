<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cost extends Model {
    use HasFactory;

    public static function read_one($id) {
        $data = self::where('id', $id)->first();
        return self::obj_to_array($data);
    }

    public static function obj_to_array($item) {
        return [
            'id' => $item->id,
            'project_id' => $item->project_id,
            'department' => $item->department,
            'sector' => $item->sector,
            'service' => $item->service,
            'person' => $item->person,
            'company' => $item->company,
            'budget' => $item->budget,
            'tax_rate' => $item->tax_rate,
            'final' => $item->final,
            'comment' => $item->comment,
        ];
    }

    public static function get_tree($id) {
        $all = self::read_all($id);
        $output = [];
        foreach ($all as $item) {
            $add=[];
            $a=1;
            array_push($data, $add);
        }
        return $data;
    }

    public static function read_all($project_id): array {
        $data = [];
        $query = self::query()->where('project_id', $project_id)->get();
        foreach ($query as $item) {
            $add = self::obj_to_array($item);
            array_push($data, $add);
        }
        return $data;
    }

    public static function update_cost($data) {
        $cost = self::find($data['id']);
        return self::write($data, $cost);
    }

    public static function delete_cost($id) {
        $item = self::find($id);
        $item->delete();
    }

    public static function create($data) {
        return self::write($data);
    }

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
        $cost->save();
        return self::obj_to_array($cost);
    }
}
