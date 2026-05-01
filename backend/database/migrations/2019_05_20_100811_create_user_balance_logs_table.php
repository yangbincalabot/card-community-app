<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBalanceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_balance_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->tinyInteger('log_type')->comment('日志操作类型：1.收入；2.支出');
            $table->tinyInteger('type')->comment('具体类型');
            $table->decimal('money')->default(0.00)->comment('操作金额');
            $table->string('remark')->nullable()->comment('操作说明');
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
        Schema::dropIfExists('user_balance_logs');
    }
}
