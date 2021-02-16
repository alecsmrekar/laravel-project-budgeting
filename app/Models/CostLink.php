<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models;

class CostLink extends Model {
    use HasFactory;

    public static function link_cost_to_transactions($pid){
        $data = [];
        //$manual_field = Cost::get_actuals_field();
        $query = self::query()
            ->join('costs', 'cost_links.cost_id', '=', 'costs.id')
            ->where('costs.project_id', '=', $pid)
            ->select('cost_links.*')
            ->get();
        foreach ($query as $item) {
            array_push($data, $item->attributes);
        }
        return $data;
    }

    // Returns a an array transactions links
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

    public static function get_all_links() {
        $data = self::all();
        $all = [];
        foreach ($data as $item) {
            array_push($all, $item->attributes);
        }
        return $all;
    }

    public static function get_linked_transactions() {
        $data = self::all();
        $all = [];
        foreach ($data as $item) {
            $provider = $item->attributes['provider'];
            if (!array_key_exists($provider, $all)) {
                $all[$provider] = [];
            }
            array_push($all[$provider], $item->attributes['transaction_id']);
        }
        return $all;
    }

    // Create a link
    public static function create($data) {
        return self::write($data);
    }

    // Delete a link
    public static function delete_link($id) {
        $item = self::find($id);
        $item->delete();
    }

    // Update a link
    public static function update_link($data) {
        $cost = self::find($data['id']);
        return self::write($data, $cost);
    }

    // Write to DB
    public static function write(array $data, $link = FALSE) {
        if ($link == FALSE) {
            $link = new self();
        }
        // For the sum, we assume we'll use the entire sum of the linked cost
        $cost = Models\Cost::read_one($data['cost_id']);

        $link->cost_id = $data['cost_id'];
        $link->transaction_id = $data['transaction_id'];
        $link->provider = $data['provider'];
        $link->amount = $cost['budget'];
        $link->save();
        return self::obj_to_array($link);
    }

    // Convert an object to an array
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
