<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumntToRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('desc')->nullable()->after('reason');
            $table->string('add_info')->nullable()->after('desc');
            $table->string('conclusion')->nullable()->after('add_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('desc', 'add_info', 'conclusion');
        });
    }
}
