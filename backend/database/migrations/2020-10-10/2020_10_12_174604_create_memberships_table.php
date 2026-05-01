<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->default(0)->index()->comment('所属会员');
            $table->bigInteger('aid')->default(0)->index()->comment('所属协会');
            $table->bigInteger('carte_id')->default(0)->index()->comment('所属名片');
            $table->tinyInteger('status')->default(0)->comment('状态, -1:审核不通过, 0:未审核, 1:审核通过');
            $table->timestamps();

            $table->index(['user_id', 'aid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memberships');
    }
}
