<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_screens', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('uid')->index()->comment('所属用户');
            $table->json('area')->nullable()->comment('区域');
            $table->json('industry')->nullable()->comment('行业');

//            $table->string('name')->default('')->comment('搜索器名称');
//            $table->string('province')->default('')->comment('省份');
//            $table->string('city')->default('')->comment('城市');
//            $table->integer('industry_id')->default(0)->comment('所在行业');
//            $table->boolean('attestation')->default(\App\Models\UserScreen::AUTHENTICATE_FALSE)->comment('是否认证');
//            $table->boolean('is_active')->default(\App\Models\UserScreen::ACTIVE_FALSE)->comment('是否激活');
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
        Schema::dropIfExists('user_screens');
    }
}
