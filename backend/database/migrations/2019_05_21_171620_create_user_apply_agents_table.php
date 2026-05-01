<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserApplyAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_apply_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index()->comment('所属用户');
            $table->integer('agent_id')->comment('代理等级id');
            $table->string('name')->comment('姓名');
            $table->string('mobile')->comment('手机号');
            $table->string('id_card')->comment('身份证');
            $table->string('province')->comment('省');
            $table->string('city')->comment('市');
            $table->string('district')->comment('区/县');
            $table->string('address')->comment('详细地址');
            $table->tinyInteger('status')->default(\App\Models\User\UserApplyAgent::APPLY_STATUS_STAY)
                ->comment('审核状态，1：成功，2：待审核，3：审核失败');
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
        Schema::dropIfExists('user_apply_agents');
    }
}
