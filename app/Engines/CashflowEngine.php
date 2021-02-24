<?php


namespace App\Engines;

use App\Http\Controllers\ProviderController;
use App\Models;
use App\Models\Cost;
use App\Models\Project;
use function PHPUnit\Framework\isNull;


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

    // Turns on the setting that indicates we want all cost data to be returned, not only actuals
    public function receiveAllCostData() {
        $this->return_all_cost_data = TRUE;
    }

    // Makes a dictionary of costs with cost ids as keys
    private function generate_indexed_cost_data() {
        foreach ($this->cost_array as $i) {
            $this->indexed_cost_data[$i['id']] = $i;
            $this->indexed_cost_data[$i['id']]['transactions'] = 0; // init actuals to 0
            $this->indexed_cost_data[$i['id']]['transactions_data'] = []; // init actuals to 0
        }
    }

    /* Makes a dictionary of transactions with
    ** providers as primary keys and transaction numbers as secondary keys
    */
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

    // Calculates actuals fields for every linked trasanction
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
                $trans['date'],
                $citem['link_tag']
            );
            array_push(
                $this->indexed_cost_data[$cid]['transactions_data'],
                $insert
            );
        }
    }

    // Returns the fields relevalnt to manual cost entry
    private function get_cost_manuals($cost_array) {
        if ($cost_array['manual_actuals_date']) {
            return [
                'actuals' => $cost_array[$this->actuals_field_name] * -1,
                'date' => $cost_array['manual_actuals_date'],
            ];
        }
        return [];
    }

    // Generate the actuals date field for one cost item
    private function get_cost_actuals_date() {
        foreach ($this->indexed_cost_data as $key => $item) {
            if ($item['manual_actuals_date']) {
                $this->indexed_cost_data[$key]['date'] = $item['manual_actuals_date'];
            }
            else {
                if (sizeof($item['transactions_data'])) {
                    $this->indexed_cost_data[$key]['date'] = $item['transactions_data'][0]['date'];
                }
                else {
                    $this->indexed_cost_data[$key]['date'] = '';
                }
            }
        }
    }

    // Main function
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
                $item['date'],
                (!$item['manual_actuals_tag'] ? '' : $item['manual_actuals_tag'])
            );

            $output[$key] = array_merge($output[$key], $calcs);
        }
        return $output;
    }

    // Main function
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
                    $manuals['date'],
                    (!$item['manual_actuals_tag'] ? '' : $item['manual_actuals_tag'])
                );
                $insert = array_merge($insert, $calc);
                array_push($output, $insert);
            }

            // Loop transactions
            foreach ($item['transactions_data'] as $tr) {
                $insert = array_merge($base_item, $tr);
                array_push($output, $insert);
            }
        }
        return $output;
    }

    // Calculates the actuals for one cashflow item
    private function calc_transaction($actuals, $budget, $tax_rate, $date, $tag='') {
        $actuals_net = round($actuals / (1 + $tax_rate), 2);
        $tax = round($actuals - $actuals_net, 2);
        $diff = $actuals + $budget;
        return [
            'actuals' => $actuals,
            'actuals_net' => $actuals_net,
            'tax_part' => $tax,
            'diff' => $diff,
            'budget' => $budget,
            'date' => $date,
            'tag' => $tag
        ];
    }
}
