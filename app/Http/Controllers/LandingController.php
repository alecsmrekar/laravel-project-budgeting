<?php

namespace App\Http\Controllers;

use App\Models;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $transactions = Models\Transaction::read_all();
        $table_headers = [];
        $table_headers = (isset($transactions[0]) ? array_keys($transactions[0]) : []);
        return view('transactions_list')->with([
            'headers' => $table_headers,
            'transactions' => $transactions
        ]);
    }
}
