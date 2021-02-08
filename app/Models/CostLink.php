<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CostLink extends Model {
    use HasFactory;

    public static function get_transaction_links($provider, $id) {
        $data = [];
        $query = self::query()
            ->where('id', $id)
            ->where('provider', $provider)
            ->get();
        foreach ($query as $item) {
            $add = self::obj_to_array($item);
            array_push($data, $add);
        }
        return $data;
    }

    public static function create($data) {
        return self::write($data);
    }

    public static function delete_link($id) {
        $item = self::find($id);
        $item->delete();
    }

    public static function update_link($data) {
        $cost = self::find($data['id']);
        return self::write($data, $cost);
    }

    public static function write(array $data, $link = FALSE) {
        if ($link == FALSE) {
            $link = new self();
        }
        $link->project_id = $data['project_id'];
        $link->department = $data['department'];
        $link->sector = $data['sector'];
        $link->save();
        return self::obj_to_array($link);
    }
}
