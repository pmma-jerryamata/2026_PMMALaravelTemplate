<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('tasks', 'sort_order')) {
            Schema::table('tasks', fn (Blueprint $table) => $table->unsignedInteger('sort_order')->default(0)->index());
            DB::table('tasks')->update(['sort_order' => DB::raw('id')]);
        }
    }

    public function down()
    {
        Schema::table('tasks', fn (Blueprint $table) => $table->dropIndex(['sort_order']));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('sort_order'));
    }
};
