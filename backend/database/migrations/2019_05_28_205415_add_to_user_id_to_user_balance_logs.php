<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddToUserIdToUserBalanceLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_balance_logs', function (Blueprint $table) {
            $table->integer('from_user_id')->default(0)->comment('收益来源的user_id');
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
            $table->dropColumn('from_user_id');
        });
    }
}
