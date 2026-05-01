<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCompanyCardRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_card_roles', function (Blueprint $table) {
            $table->unsignedInteger('carte_id')->default(0)->index()->comment('名片id');
            $table->boolean('is_company')->default(\App\Models\CompanyCardRole::IS_COMPANY_TRUE)->index()->comment('是否企业认证');
            $table->string('avatar_url')->nullable()->comment('申请时提交的图片');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_card_roles', function (Blueprint $table) {
            $table->dropColumn('carte_id');
            $table->dropColumn('is_company');
            $table->dropColumn('avatar_url');
        });
    }
}
