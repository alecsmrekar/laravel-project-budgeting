<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsRevolutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions_revolut', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->timestamp('time');
            $table->string('type');
            $table->float('amount');
            $table->string('account');
            $table->string('counterparty');
            $table->string('currency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions_revolut');
    }
}
