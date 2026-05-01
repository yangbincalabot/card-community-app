<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeeToCompanyRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_role', function (Blueprint $table) {
            $table->unsignedDecimal('fee')->default(0)->comment('申请角色费用')->after('sort');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_role', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
}
