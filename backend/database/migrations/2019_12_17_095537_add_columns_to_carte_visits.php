<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCarteVisits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('carte_visits', function (Blueprint $table) {
            $table->unsignedInteger('week_nums')->default(0)->comment('本周浏览数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('carte_visits', function (Blueprint $table) {
            $table->dropColumn('week_nums');
        });
    }
}
