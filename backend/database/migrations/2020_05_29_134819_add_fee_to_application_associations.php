<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\ApplicationAssociation;

class AddFeeToApplicationAssociations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_associations', function (Blueprint $table) {
            $table->unsignedDecimal('fee')->default(0)->comment('支付费用')->after('aid');
            $table->unsignedTinyInteger('pay_type')->default(ApplicationAssociation::PAY_TYPE_BALANCE)->index()->comment('支付类型');
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
            $table->dropColumn('fee');
            $table->dropColumn('pay_type');
        });
    }
}
