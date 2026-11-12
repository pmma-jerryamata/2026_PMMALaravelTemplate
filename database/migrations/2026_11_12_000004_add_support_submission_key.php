<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->string('submission_key', 64)->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->dropUnique(['submission_key']);
            $table->dropColumn('submission_key');
        });
    }
};
