<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiveCartesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receive_cartes', function (Blueprint $table) {
            $table->softDeletes();
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('用户id');
            $table->integer('from_user_id')->comment('来源用户id');
            $table->tinyInteger('type')->default(\App\Models\ReceiveCarte::TYPE_SCAN)->comment('名片来源类型,1-扫描,2-对方传递');
            $table->string('address_title')->nullable()->comment('详细地址(主要用于扫描)');
            $table->string('address_name')->nullable()->default('')->comment('地址简称(主要用于扫描)');
            $table->string('longitude')->nullable()->default('')->comment('经度');
            $table->string('latitude')->nullable()->default('')->comment('纬度');
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
        Schema::dropIfExists('receive_cartes');
    }
}
