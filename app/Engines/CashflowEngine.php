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
    private $register;
    private $indexed_cost_data = [];
    private $indexed_transaction_data = [];
    private $return_all_cost_data = FALSE;

    // use the provider controller to read actuals
    // use the cost model to read manuals

    function __construct($project_id = FALSE, $cost_id = FALSE) {
        $this->pid = $project_id;
        $this->cid = $cost_id;
        $this->actuals_field_name = Models\Cost::get_actuals_field();
        $this->cost_array = Models\Cost::get_cost_array($this->pid, $this->cid);
        $this->links = Models\CostLink::link_cost_to_transactions($this->pid, $this->cid);
        $this->transactions = ProviderController::read_transactions(FALSE);
        $this->register = ProviderController::$register;
        $this->generate_indexed_cost_data();
        $this->generate_indexed_transaction_data();
    }

    public function receiveAllCostData() {
        $this->return_all_cost_data = TRUE;
    }

    private function generate_indexed_cost_data() {
        foreach ($this->cost_array as $i) {
            $this->indexed_cost_data[$i['id']] = $i;
            $this->indexed_cost_data[$i['id']]['transactions'] = 0; // init actuals to 0
            $this->indexed_cost_data[$i['id']]['transactions_data'] = []; // init actuals to 0
        }
    }

    private function generate_indexed_transaction_data() {
        foreach ($this->transactions as $item) {
            $provider = $item['provider'];
            $provider_id_key = $this->register[$provider]::get_table_id_field();
            if (!array_key_exists($provider, $this->indexed_transaction_data)) {
                $this->indexed_transaction_data[$provider] = [];
            }
            $this->indexed_transaction_data[$provider][$item[$provider_id_key]] = [
                'amount' => $item['amount'],
                'date' => $item['time']
            ];
        }
    }

    public function get_actuals() {
        $output = [];

        // Add the transaction amounts
        foreach ($this->links as $ckey => $citem) {
            $provider = $citem['provider'];
            $tid = $citem['transaction_id'];
            $cid = $citem['cost_id'];
            $this->indexed_cost_data[$cid]['transactions'] += $this->indexed_transaction_data[$provider][$tid]['amount'];
            $trans = $this->indexed_transaction_data[$provider][$tid];
            $insert = $this->calc_transaction(
                floatval($trans['amount']),
                floatval($this->indexed_cost_data[$cid]['budget']),
                floatval($this->indexed_cost_data[$cid]['tax_rate']),
                $trans['date']
            );
            array_push(
                $this->indexed_cost_data[$cid]['transactions_data'],
                $insert
            );
        }

        // Sum it up and calc the diff
        foreach ($this->indexed_cost_data as $key => $item) {

            // transactions will be negative
            $actuals = $item[$this->actuals_field_name] - $item['transactions'];
            $net_actuals = $actuals / (1 + $item['tax_rate']);
            $net_actuals = round($net_actuals, 2);
            $tax = round($actuals - $net_actuals, 2);
            $output[$key] = [];
            if ($this->return_all_cost_data) {
                $output[$key] = $item;

                // Invert the manuals because they are entred as a positive number
                $output[$key][$this->actuals_field_name] *= -1;
            }

            $calcs = $this->calc_transaction(
                $actuals,
                $item['budget'],
                $item['tax_rate'],
                $item['manual_actuals_date']
            );

            $output[$key] = array_merge($output[$key], $calcs);



            //$output[$key]['actuals_net'] *= -1;
            //$output[$key]['tax_part'] *= -1;
        }
        return $output;
    }

    private function calc_transaction($actuals, $budget, $tax_rate, $date) {
        $actuals_net = round($actuals / (1 + $tax_rate), 2);
        $tax = round($actuals - $actuals_net,2);
        $diff = $budget - $actuals;
        return [
            'actuals' => $actuals,
            'actuals_net' => $actuals_net,
            'tax_part' => $tax,
            'diff' => $diff,
            'budget' => $budget,
            'date' => $date
        ];
    }
}
