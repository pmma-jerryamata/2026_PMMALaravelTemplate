<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        foreach (['employees', 'technicians'] as $name) {
            if (! Schema::hasColumn($name, 'directory_archived_at')) {
                Schema::table($name, fn (Blueprint $table) => $table->timestamp('directory_archived_at')->nullable()->index());
            }
        }
    }

    public function down()
    {
        foreach (['employees', 'technicians'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('directory_archived_at'));
        }
    }
};
