<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', fn (Blueprint $table) => $table->unsignedInteger('display_order')->default(0)->index());
        foreach (DB::table('service_categories')->orderBy('id')->pluck('id') as $index => $id) {
            DB::table('service_categories')->where('id', $id)->update(['display_order' => $index + 1]);
        }
    }
    public function down(): void
    {
        Schema::table('service_categories', fn (Blueprint $table) => $table->dropColumn('display_order'));
    }
};
