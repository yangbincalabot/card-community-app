<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToApplicationAssociations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_associations', function (Blueprint $table) {
            $table->unsignedTinyInteger('type')->default(\App\Models\ApplicationAssociation::TYPE_PERSONAL)->index()->after('reason');
            $table->string('avatar_url')->nullable()->comment('会员头像')->after('type');
            $table->unsignedInteger('carte_id')->default(0)->index()->comment('名片id')->after('avatar_url');
            $table->unsignedInteger('role_id')->default(0)->index()->comment('角色id')->after('carte_id');
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
            $table->dropColumn('type');
            $table->dropColumn('avatar_url');
            $table->dropColumn('carte_id');
            $table->dropColumn('role_id');
        });
    }
}
