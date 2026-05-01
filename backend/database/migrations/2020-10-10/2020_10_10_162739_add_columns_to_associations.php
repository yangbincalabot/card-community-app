<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToAssociations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('associations', function (Blueprint $table) {
            $table->json('service_images')->nullable()->comment('服务简介图片集')->after('desc');
            $table->longText('service_desc')->nullable()->comment('服务简介')->after('service_images');
            $table->json('member_wall')->nullable()->comment('会员墙')->after('service_desc');
            $table->longText('contact_us')->nullable()->comment('联系我们')->after('member_wall');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('associations', function (Blueprint $table) {
            foreach (['service_images','service_desc', 'member_wall', 'contact_us'] as $field) {
                $table->dropColumn($field);
            }
        });
    }
}
