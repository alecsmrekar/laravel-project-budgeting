<?php


namespace App\Engines;

use App\Http\Controllers\ProviderController;
use App\Models;
use App\Models\Cost;
use App\Models\Project;


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
    private $project_names = [];

    // use the provider controller to read actuals
    // use the cost model to read manuals

    function __construct($project_id = FALSE, $cost_id = FALSE) {
        $this->pid = $project_id;
        $this->cid = $cost_id;
        $this->project_names = Project::get_project_names();
        $this->actuals_field_name = Models\Cost::get_actuals_field();
        $this->cost_array = Models\Cost::get_cost_array($this->pid, $this->cid);
        $this->links = Models\CostLink::link_cost_to_transactions($this->pid, $this->cid);
        $this->transactions = ProviderController::read_transactions(FALSE);
        $this->register = ProviderController::$register;
        $this->generate_indexed_cost_data();
        $this->generate_indexed_transaction_data();
        $this->get_cost_actuals_date();
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
        $this->generate_transaction_calcs();
    }

    private function generate_transaction_calcs() {
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
    }

    private function get_cost_manuals($cost_array) {
        if ($cost_array['manual_actuals_date']) {
            return [
                'actuals' => $cost_array[$this->actuals_field_name]*-1,
                'date' => $cost_array['manual_actuals_date'],
            ];
        }
        return [];
    }

    // Priority to manuals date
    private function get_cost_actuals_date() {
        foreach ($this->indexed_cost_data as $key => $item) {
            if ($item['manual_actuals_date']) {
                $this->indexed_cost_data[$key]['date'] = $item['manual_actuals_date'];
            }
            else if (sizeof($item['transactions_data'])) {
                $this->indexed_cost_data[$key]['date'] = $item['transactions_data'][0]['date'];
            }
            else {
                $this->indexed_cost_data[$key]['date'] = '';
            }
        }
    }

    public function get_actuals() {
        $output = [];

        foreach ($this->indexed_cost_data as $key => $item) {

            $output[$key] = [];
            if ($this->return_all_cost_data) {
                $output[$key] = $item;
            }
            $manuals = $this->get_cost_manuals($item);
            $actuals = $item['transactions'];
            if (sizeof($manuals)) {
                $actuals += $manuals['actuals'];
            }

            $calcs = $this->calc_transaction(
                $actuals,
                $item['budget'],
                $item['tax_rate'],
                $item['date']
            );

            $output[$key] = array_merge($output[$key], $calcs);
        }
        return $output;
    }

    public function get_actuals_by_event() {
        $output = [];

        foreach ($this->indexed_cost_data as $key => $item) {

            $base_item = [
                'cost_id' => $item['id'],
                'project' => $this->project_names[$item['project_id']],
                'project_id' => $item['project_id'],
                'department' => $item['department'],
                'sector' => $item['sector'],
                'service' => $item['service'],
                'person' => $item['person'],
                'company' => $item['company'],
                'budget' => $item['budget'],
                'tax_rate' => $item['tax_rate'],
                'final' => $item['final'],
                'comment' => $item['comment'],
                'cost' => Cost::generate_service_title($item),
            ];

            // First check for manuals
            $manuals = $this->get_cost_manuals($item);
            if (sizeof($manuals)) {
                $insert = $base_item;
                $calc = $this->calc_transaction($manuals['actuals'],
                $item['budget'],
                    $item['tax_rate'],
                    $manuals['date']);
                $insert = array_merge($insert, $calc);
                array_push($output, $insert);
            }

            // Loop transactions
            foreach ($item['transactions_data'] as $tr) {
                $calc = $this->calc_transaction(
                    $tr['actuals'],
                    $item['budget'],
                    $item['tax_rate'],
                    $tr['date']);
                $insert = array_merge($base_item, $calc);
                array_push($output, $insert);
            }
            $a=2;
        }
        return $output;
    }

    private function calc_transaction($actuals, $budget, $tax_rate, $date) {
        $actuals_net = round($actuals / (1 + $tax_rate), 2);
        $tax = round($actuals - $actuals_net,2);
        $diff = $actuals + $budget;
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
