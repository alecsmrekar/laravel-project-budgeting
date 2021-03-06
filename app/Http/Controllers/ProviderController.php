<?php


namespace App\Http\Controllers;

use App\Models;


class ProviderController {

    // Here we register all the providers
    static $register = [
        'Revolut' => Models\Revolut::class,
    ];

    // Returns an array of all existing transactions from providers and marks the linked ones
    public static function read_transactions($find_links=true) {
        $all_items = [];
        $links = [];

        // If true, we want to mark the transactions which have links
        if ($find_links) {
            $links = Models\CostLink::get_linked_transactions();
        }
        foreach (self::$register as $id=>$provider) {
            $data = $provider::read_local_transactions();
            if ($find_links) {
                foreach ($data as $key => $transaction) {
                    $provider_id = $transaction['provider'];
                    $id_key = $provider::get_table_id_field();
                    $data[$key]['status'] = 'Not Linked';
                    if (array_key_exists($provider_id, $links) && in_array($transaction[$id_key], $links[$provider_id])) {
                        $data[$key]['status'] = 'Linked';
                    }
                }
            }
            $all_items = array_merge($all_items, $data);
        }
        return $all_items;
    }


}
