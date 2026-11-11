<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('supports', 'priority')) {
            Schema::table('supports', function (Blueprint $table) {
                $table->string('priority', 10)->default('Normal')->index();
            });
        }
    }

    public function down()
    {
        Schema::table('supports', fn (Blueprint $table) => $table->dropColumn('priority'));
    }
};
