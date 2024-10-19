<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('refund_id')->nullable()->constrained()->onDelete('cascade'); // Added refund_id
            $table->string('reason');
            $table->tinyInteger('status')->default(0)->comment('open: 0, resolve:1');
            $table->string('image')->nullable(); // Store the path for the image
            $table->string('video')->nullable(); // Store the path for the video
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disputes');
    }
}
