<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCardLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_card_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('所属用户');
            $table->decimal('money')->comment('金额');
            $table->string('order_sn')->comment('订单编号');
            $table->string('payment_no')->nullable()->comment('支付订单号');
            $table->string('remark')->nullable()->comment('备注');
            $table->tinyInteger('pay_type')->default(\App\Models\CompanyCardLog::PAY_WECHAT)->comment('支付类型，1-微信支付，2-余额支付');
            $table->tinyInteger('is_pay')->default(0)->comment('是否支付');
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->softDeletes();
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
        Schema::dropIfExists('company_card_logs');
    }
}
