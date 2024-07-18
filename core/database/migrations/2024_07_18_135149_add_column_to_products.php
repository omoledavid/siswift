<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('ram')->nullable();
            $table->string('condition')->nullable();
            $table->string('sim')->nullable();
            $table->string('state')->nullable();
            $table->string('lga')->nullable();
            $table->text('bulk_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('ram','condition','sim','state','lga','bulk_price');
        });
    }
}
