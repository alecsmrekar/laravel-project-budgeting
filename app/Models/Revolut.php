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

    // Returns an array of all the transactions and the associated links
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

    public static function get_id() {
        return 'Revolut';
    }

    public static function get_table_id_field() {
        return 'number';
    }
}
