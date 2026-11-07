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
        Schema::create('supports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id'); //F-key
            $table->bigInteger('technician_id'); //F-key
            $table->bigInteger('task_id'); //F-key
            $table->string('status');
            $table->longText('support_specifics')->nullable();
            $table->longText('findings')->nullable();
            $table->string('ip');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supports');
    }
};
