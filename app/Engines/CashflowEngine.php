<?php


namespace App\Engines;

use App\Http\Controllers\ProviderController;
use App\Models;


class CashflowEngine {

    // use the provider controller to read actuals
    // use the cost model to read manuals

    public static function get_project_actuals($project_id, $cost_data) {
        $cost_links = Models\CostLink::link_cost_to_transactions($project_id);
        $keyed_transactions = [];
        $register = ProviderController::$register;
        $transactions = ProviderController::read_transactions(false);
        foreach ($transactions as $item) {
            $provider = $item['provider'];
            $provider_id_key = $register[$provider]::get_table_id_field();
            if (!array_key_exists($provider, $keyed_transactions)) {
                $keyed_transactions[$provider] = [];
            }
            $keyed_transactions[$provider][$item[$provider_id_key]] = $item['amount'];
        }
        foreach ($cost_data as $key=>$item) {
            $cost_data[$key]['transactions'] = 0;
        }
        foreach ($cost_links as $ckey=>$citem) {
            $provider = $citem['provider'];
            $tid = $citem['transaction_id'];
            $cid = $citem['cost_id'];
            if (isset($keyed_transactions[$provider][$tid])) {
                $cost_data[$cid]['transactions'] += $keyed_transactions[$provider][$tid];
            }
        }
        $output = [];
        foreach ($cost_data as $key=>$item) {
            $actuals = $item['manual'] + $item['transactions'];
            $diff = $item['budget'] - $actuals;
            $output[$key] = [
                'budget' => $item['budget'],
                'actuals' => $actuals,
                'diff' => $diff,
            ];
        }
        return $output;
    }

}
