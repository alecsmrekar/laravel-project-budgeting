<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models;
use PhpParser\Node\Expr\AssignOp\Mod;

class CostLink extends Model {
    use HasFactory;

    public static function get_transaction_links($provider, $id) {
        $data = [];
        $query = self::query()
            ->where('transaction_id', $id)
            ->where('provider', $provider)
            ->join('costs', 'cost_links.cost_id', '=', 'costs.id')
            ->select('cost_links.*', 'costs.project_id', 'costs.department', 'costs.service')
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
        // load cost
        $cost = Models\Cost::read_one($data['cost_id']);

        $link->cost_id = $data['cost_id'];
        $link->transaction_id = $data['transaction_id'];
        $link->provider = $data['provider'];
        $link->amount = $cost['budget'];
        $link->save();
        return self::obj_to_array($link);
    }

    public static function obj_to_array($item) {
        return [
            'id' => $item->id,
            'cost_id' => $item->cost_id,
            'transaction_id' => $item->transaction_id,
            'provider' => $item->provider,
            'amount' => $item->amount,
            'project_id' => $item->project_id,
            'department' => $item->department,
            'service' => $item->service,
        ];
    }
}
