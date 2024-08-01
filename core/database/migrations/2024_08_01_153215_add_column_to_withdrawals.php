<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToWithdrawals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->integer('account_number')->after('amount');
            $table->tinyInteger('bank_code')->after('account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('account_number', 'bank_code');
        });
    }
}
