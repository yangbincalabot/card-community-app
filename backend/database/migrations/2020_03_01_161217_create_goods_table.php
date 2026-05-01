<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index()->comment('用户id');
            $table->string('title')->comment('商品标题');
            $table->decimal('price')->default(0)->comment('商品价格');
            $table->string('image')->comment('商品图片');
            $table->json('images')->nullable()->comment('商品图片集');
            $table->longText('content')->nullable()->comment('商品详情');
            $table->unsignedMediumInteger('sales')->default(0)->comment('销量');
            $table->unsignedMediumInteger('views')->default(0)->comment('浏览量');
            $table->boolean('is_show')->default(\App\Models\Goods::IS_SHOW_TRUE)->comment('是否显示');
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
        Schema::dropIfExists('goods');
    }
}
