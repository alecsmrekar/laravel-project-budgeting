<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Interfaces\ProviderInterface;

class Revolut extends Model implements ProviderInterface{
    use HasFactory;

    protected $table = 'transactions_revolut';

    public static function obj_to_array($item, $actuals=false) {
        $base = $item->attributes;
        if (isset($base['lid'])) {
            $base['status'] = ($base['lid'] ? 'Linked' : 'Not Linked');
        }
        $base['provider'] = self::get_id();
        if (array_key_exists('created_at', $base)) {
            unset($base['created_at']);
        }
        if (array_key_exists('updated_at', $base)) {
            unset($base['updated_at']);
        }
        return $base;
    }

    // move to cashflow engine
    public static function get_cost_actuals($cid) {
        $items = [];
        $query = self::query_actuals(false, $cid);
        foreach ($query as $item) {
            $add = self::obj_to_array($item, TRUE);
            $items[$item['cid']] = $add;
        }
        return $items;
    }

    // move to cashflow engine
    public static function get_project_actuals($id) {
        $items = [];
        $query = self::query_actuals($pid=$id);
        foreach ($query as $item) {
            $add = self::obj_to_array($item, TRUE);
            $items[$item['cid']] = $add;
        }
        return $items;
    }

    // move to cashflow engine
    public static function get_all_actuals() {
        $items = [];
        $query = self::query_actuals();
        foreach ($query as $item) {
            $add = self::obj_to_array($item, TRUE);
            $items[$item['cid']] = $add;
        }
        return $items;
    }

    // move to cashflow engine
    private static function query_actuals($pid = FALSE, $cid = FALSE) {
        $query = self::query();
        $provider = 'Revolut';
        $select = ['transactions_revolut.*',
            'costs.project_id as pid',
            'costs.department',
            'costs.sector',
            'costs.id as cid',
            'cost_links.id as lid'];
        $query->leftJoin('cost_links', function ($join) use ($provider) {
            $join->on('transactions_revolut.number', '=', 'cost_links.transaction_id');
            $join->where('cost_links.provider', '=', $provider);

        });
        $query->leftJoin('costs', 'costs.id', '=', 'cost_links.cost_id');
        if ($pid) {
            $query->where('costs.project_id', '=', $pid);
        }
        if ($cid) {
            $query->where('cost_links.cost_id', '=', $cid);
        }
        $query->select($select);
        return $query->get();
    }

    // todo think about integration
    public static function read_local_transactions() {
        $items = [];
        $provider = 'Revolut';
        $query = self::query()
            ->leftJoin('cost_links', function ($join) use ($provider) {
                $join->on('transactions_revolut.number', '=', 'cost_links.transaction_id');
                $join->where('cost_links.provider', '=', $provider);
            })
            ->select('transactions_revolut.*', 'cost_links.id as lid')
            ->get();
        foreach ($query as $item) {
            $add = self::obj_to_array($item);
            array_push($items, $add);
        }
        return $items;
    }

    // todo think about integration
    public static function read_local_transactions2() {
        $items = [];
        foreach (self::all() as $item) {
            $add = self::obj_to_array($item);
            array_push($items, $add);
        }
        return $items;
    }

    public static function get_id() {
        return 'Revolut';
    }

    public static function get_table_id_field() {
        return 'number';
    }
}
