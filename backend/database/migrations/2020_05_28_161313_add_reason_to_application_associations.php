<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonToApplicationAssociations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_associations', function (Blueprint $table) {
            $table->string('reason')->nullable()->default('')->comment('申请理由')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('application_associations', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
}
