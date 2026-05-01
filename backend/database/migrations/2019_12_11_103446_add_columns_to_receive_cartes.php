<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToReceiveCartes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receive_cartes', function (Blueprint $table) {
            $table->string('message')->nullable()->default('')->comment('留言');
            $table->tinyInteger('is_adding')->default(\App\Models\ReceiveCarte::NOT_REVIEWED)->comment('是否通过');
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
            $table->dropColumn('message');
            $table->dropColumn('is_adding');
        });
    }
}
