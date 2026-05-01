<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyBindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_binds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('用户id')->index();
            $table->integer('company_id')->comment('公司id')->index();
            $table->integer('carte_id')->comment('名片id')->index();
            $table->tinyInteger('status')->default(0)->comment('0-未审核， 1-审核成功， 2-审核失败');
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
        Schema::dropIfExists('company_binds');
    }
}
