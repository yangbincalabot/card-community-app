<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adv_positions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('位置名称');
            $table->string('flag')->comment('位置标识')->index();
            $table->string('describe')->comment('描述')->nullable();
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
        Schema::dropIfExists('adv_positions');
    }
}
