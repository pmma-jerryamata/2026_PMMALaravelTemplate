<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('employees', 'activation_pending')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->boolean('activation_pending')->default(false)->index();
            });
        }
        if (! Schema::hasTable('service_issues')) {
            Schema::create('service_issues', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id')->index();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('service_issues');
        Schema::table('employees', fn (Blueprint $table) => $table->dropIndex(['activation_pending']));
        Schema::table('employees', fn (Blueprint $table) => $table->dropColumn('activation_pending'));
    }
};
