<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraws', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index()->comment('所属用户');
            $table->integer('bank_id')->comment('绑定的银行卡');
            $table->string('card_name')->comment('持卡人姓名');
            $table->string('card_number')->comment('银行卡号');
            $table->decimal('money')->comment('提现金额');
            $table->tinyInteger('status')->default(\App\Models\Withdraw::WITHDRAW_STATUS_STAY)->comment('状态, 1-提现成功， 2-未审核， 3-提现失败');
            $table->string('remark')->nullable()->comment('备注');
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
        Schema::dropIfExists('withdraws');
    }
}
