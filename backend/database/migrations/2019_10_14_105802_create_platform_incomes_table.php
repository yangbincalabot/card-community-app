<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_incomes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->default(0)->comment('所属用户');
            $table->tinyInteger('type')->default(\App\Models\PlatformIncome::COMPANY_TYPE)->comment("收益类型，1-开通企业会员，2-活动分佣");
            $table->decimal('money')->default(0)->comment("金额");
            $table->integer('info_id')->default(0)->comment("关联的数据id, 比如活动的id");
            $table->string('remark')->default('')->comment("备注说明");
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
        Schema::dropIfExists('platform_incomes');
    }
}
