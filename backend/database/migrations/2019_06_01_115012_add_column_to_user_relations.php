<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToUserRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_relations', function (Blueprint $table) {
            $table->integer('level')->default(1)->comment('等级');
            $table->string('path')->default('')->comment('节点路径');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_relations', function (Blueprint $table) {
            $table->dropColumn('level');
            $table->dropColumn('path');
        });
    }
}
