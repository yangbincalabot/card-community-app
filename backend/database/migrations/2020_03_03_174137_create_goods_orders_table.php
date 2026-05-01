<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('goods_id')->default(0)->index()->comment('商品id');
            $table->integer('user_id')->default(0)->index()->comment('下单的用户');
            $table->unsignedDecimal('price')->default(0)->comment('下单的价格');
            $table->boolean('is_pay')->default(\App\Models\GoodsOrder::IS_PAY_FALSE)->comment('是否支付');
            $table->timestamp('payed_at')->nullable()->comment('付款时间');
            $table->string('order_sm')->index()->nullable()->comment('订单流水');
            $table->string('pay_sm')->index()->nullable()->comment('平台支付流水');
            $table->unsignedInteger('num')->default(1)->comment('购买数量');
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
        Schema::dropIfExists('goods_orders');
    }
}
