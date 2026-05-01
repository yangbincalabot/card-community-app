<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Association;

class CreateAssociationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('associations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(0)->index()->comment('所属用户，后台添加为0');
            $table->string('name')->nullable()->default('')->comment('协会名称');
            $table->string('image')->nullable()->default('')->comment('协会logo图');

            $table->unsignedTinyInteger('status')->index()->default(Association::STATUS_NOT_REVIEWED)->comment('审核状态');
            $table->string('remark')->nullable()->default('')->comment('备注');
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
        Schema::dropIfExists('associations');
    }
}
