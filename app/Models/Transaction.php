<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public static function read_all(): array {
        $provider_list = [
            'Revolut' => new Revolut()
        ];
        $transactions = [];
        foreach ($provider_list as $key => $provider) {
            $transactions = array_merge($transactions, $provider->read_local_transactions());
        }
        return $transactions;
    }
}
