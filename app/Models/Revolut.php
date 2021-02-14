<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revolut extends Model
{
    use HasFactory;
    protected $table = 'transactions_revolut';

    public static function obj_to_array($item) {
        return [
            'id' => $item->number,
            'time' => $item->time,
            'type' => $item->type,
            'account' => $item->account,
            'amount' => $item->amount,
            'counterparty' => $item->counterparty,
            'currency' => $item->currency,
            'provider' =>  'Revolut',
            'status' => ($item->lid ? 'Linked' : 'Not Linked')
        ];
    }

    public function read_local_transactions() {
        $items = [];
        $provider = 'Revolut';
        $query = self::query()
            ->leftJoin('cost_links', function ($join) use($provider) {
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
