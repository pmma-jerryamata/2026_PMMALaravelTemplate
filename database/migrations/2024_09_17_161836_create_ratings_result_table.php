<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings_result', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('support_id'); //F-key
            $table->bigInteger('rating_measure_id'); //F-key
            $table->bigInteger('rating_id'); //F-key
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
        Schema::dropIfExists('ratings_result');
    }
};
