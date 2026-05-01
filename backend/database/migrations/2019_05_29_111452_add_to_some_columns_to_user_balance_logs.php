<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToSomeColumnsToUserBalanceLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_balance_logs', function (Blueprint $table) {
            $table->integer('order_id')->default(0)->comment('销售奖励关联的订单id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_balance_logs', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
}
