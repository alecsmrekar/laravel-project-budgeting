<?php


namespace App\Engines;

use App\Http\Controllers\ProviderController;
use App\Models;


class CashflowEngine {

    private $pid;
    private $cid;
    private $actuals_field_name;
    private $cost_array;
    private $links;
    private $transactions;

    // use the provider controller to read actuals
    // use the cost model to read manuals


    function __construct($project_id, $cost_id) {
        $this->pid = $project_id;
        $this->cid = $cost_id;
        $this->actuals_field_name = Models\Cost::get_actuals_field();
        $this->cost_array = Models\Cost::get_cost_array($this->pid, $this->cid);
        $this->links = Models\CostLink::link_cost_to_transactions($this->pid, $this->cid);
        $this->transactions = ProviderController::read_transactions(FALSE);
    }

    public function get_actuals() {
        $output = [];

        // Extract only relevant data from the cost array
        $cost_data = [];
        foreach ($this->cost_array as $i) {
            $cost_data[$i['id']] = [
                'manual' => $i[$this->actuals_field_name],
                'budget' => $i['budget'],
            ];
        }

        // Create an indexed table of transactions
        $keyed_transactions = [];
        $register = ProviderController::$register;
        foreach ($this->transactions as $item) {
            $provider = $item['provider'];
            $provider_id_key = $register[$provider]::get_table_id_field();
            if (!array_key_exists($provider, $keyed_transactions)) {
                $keyed_transactions[$provider] = [];
            }
            $keyed_transactions[$provider][$item[$provider_id_key]] = $item['amount'];
        }

        // Initialize actuals to zero
        foreach ($cost_data as $key => $item) {
            $cost_data[$key]['transactions'] = 0;
        }

        // Add the transaction amounts
        foreach ($this->links as $ckey => $citem) {
            $provider = $citem['provider'];
            $tid = $citem['transaction_id'];
            $cid = $citem['cost_id'];
            $cost_data[$cid]['transactions'] += $keyed_transactions[$provider][$tid];
        }

        // Sum it up and calc the diff
        foreach ($cost_data as $key => $item) {
            $actuals = $item['manual'] + $item['transactions'];
            $diff = $item['budget'] - $actuals;
            $output[$key] = [
                'actuals' => $actuals,
                'diff' => $diff,
            ];
        }
        return $output;
    }
}
