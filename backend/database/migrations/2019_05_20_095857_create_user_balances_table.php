<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->decimal('money')->default(0.00)->comment('可用余额');
            $table->decimal('frozen_money')->default(0.00)->comment('冻结金额');
            $table->decimal('reward_money')->default(0.00)->comment('推荐代理奖励');
            $table->decimal('sales_money')->default(0.00)->comment('销售提成');
            $table->decimal('total_revenue')->default(0.00)->comment('总收益');
            $table->string('key')->comment('余额校验密钥');
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
        Schema::dropIfExists('user_balances');
    }
}
