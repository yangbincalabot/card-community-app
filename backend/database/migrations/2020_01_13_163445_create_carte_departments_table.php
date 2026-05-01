<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarteDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carte_departments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('uid')->comment('所属用户')->index();
            $table->integer('department_id')->comment('所属部门')->index();
            $table->integer('carte_id')->comment('用户名片')->index();
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
        Schema::dropIfExists('carte_departments');
    }
}
