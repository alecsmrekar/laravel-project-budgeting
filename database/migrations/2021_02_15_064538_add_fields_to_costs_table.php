<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->float('manual_actuals')->default(0)->nullable();;
            $table->string('manual_actuals_tag')->default('')->nullable();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('costs', function (Blueprint $table) {
            $table->dropColumn('manual_actuals');
            $table->dropColumn('manual_actuals_tag');
        });
    }
}
