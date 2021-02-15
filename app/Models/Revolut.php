<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revolut extends Model {
    use HasFactory;

    protected $table = 'transactions_revolut';

    public static function obj_to_array($item, $actuals=false) {
        $base = [
            'id' => $item->number,
            'time' => $item->time,
            'type' => $item->type,
            'account' => $item->account,
            'amount' => $item->amount,
            'counterparty' => $item->counterparty,
            'currency' => $item->currency,
            'provider' => 'Revolut',
            'status' => ($item->lid ? 'Linked' : 'Not Linked'),
        ];
        if ($actuals) {
            $base['cid'] = $item->cid;
            $base['pid'] = $item->pid;
            $base['sector'] = $item->sector;
            $base['department'] = $item->department;
        }
        return $base;
    }

    public static function get_cost_actuals($cid) {
        $items = [];
        $query = self::query_actuals(false, $cid);
        foreach ($query as $item) {
            $add = self::obj_to_array($item, TRUE);
            $items[$item['cid']] = $add;
        }
        return $items;
    }

    public static function get_project_actuals($id) {
        $items = [];
        $query = self::query_actuals($pid=$id);
        foreach ($query as $item) {
            $add = self::obj_to_array($item, TRUE);
            $items[$item['cid']] = $add;
        }
        return $items;
    }

    public static function get_all_actuals() {
        $items = [];
        $query = self::query_actuals();
        foreach ($query as $item) {
            $add = self::obj_to_array($item, TRUE);
            $items[$item['cid']] = $add;
        }
        return $items;
    }

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

    public function read_local_transactions() {
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
}
