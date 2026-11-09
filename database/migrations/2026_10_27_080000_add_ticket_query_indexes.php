<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['created_at', 'id'], 'tickets_created_page');
            $table->index(['status', 'created_at'], 'tickets_status_created');
            $table->index(['department_id', 'created_at'], 'tickets_department_created');
            $table->index(['category', 'created_at'], 'tickets_category_created');
        });
    }
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            foreach (['tickets_created_page', 'tickets_status_created', 'tickets_department_created', 'tickets_category_created'] as $index) $table->dropIndex($index);
        });
    }
};
