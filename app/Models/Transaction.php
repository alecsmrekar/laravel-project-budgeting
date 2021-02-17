<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    // todo obsolete, used by /api/transactions/all
    public static function read_all(): array {
        $provider_list = [
            'Revolut' => Revolut::class
        ];
        $transactions = [];
        foreach ($provider_list as $key => $provider) {
            $transactions = array_merge($transactions, $provider::read_local_transactions());
        }
        return $transactions;
    }
}
