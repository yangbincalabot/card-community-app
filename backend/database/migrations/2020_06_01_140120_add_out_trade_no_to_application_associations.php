<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOutTradeNoToApplicationAssociations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_associations', function (Blueprint $table) {
            $table->string('out_trade_no')->nullable()->default('')->index()->comment('单号')->after('aid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_associations', function (Blueprint $table) {
            $table->dropColumn('out_trade_no');
        });
    }
}
