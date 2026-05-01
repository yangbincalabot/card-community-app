<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCardRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_card_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('company_id')->default(0)->index()->comment('公司id');
            $table->bigInteger('role_id')->default(0)->index()->comment('角色id');
            $table->bigInteger('aid')->default(0)->index()->comment('协会id');
            $table->unsignedInteger('role_sort')->default(9999);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_card_roles');
    }
}
