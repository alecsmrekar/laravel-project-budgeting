<?php

namespace App\Models;

use App\Http\Controllers\CostController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostLink extends Model {
    use HasFactory;

    // Find costs within a projects which are linked to transactions
    public static function link_cost_to_transactions($pid=false, $cid=false) {
        $data = [];
        $query = self::query()->join('costs', 'costs.id', '=', 'cost_links.cost_id');
        if ($pid) {
            $query->where('costs.project_id', '=', $pid);
        }
        if ($cid) {
            $query->where('costs.id', '=', $cid);
        }
        $query->select('cost_links.*');
        foreach ($query->get() as $item) {
            array_push($data, $item->attributes);
        }
        return $data;
    }

    // Returns all links that have been added onto a single transaction
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

    // Returns an array of transactions id (per provider) which have links
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

        $link->cost_id = $data['cost_id'];
        $link->transaction_id = $data['transaction_id'];
        $link->provider = $data['provider'];
        $link->link_tag = $data['link_tag'];
        $link->save();
        return self::obj_to_array($link);
    }

    // Convert an object to an array
    public static function obj_to_array($item) {
        return (array) $item->attributes;
    }
}
