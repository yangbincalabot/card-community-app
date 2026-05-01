<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShareUserIdToReceiveCartes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receive_cartes', function (Blueprint $table) {
            $table->integer('share_user_id')->default(0)->comment('分享用户id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receive_cartes', function (Blueprint $table) {
            $table->dropColumn('share_user_id');
        });
    }
}
