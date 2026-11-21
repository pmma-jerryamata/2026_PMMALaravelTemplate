<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_receipts', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->string('resource_type');
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_receipts');
    }
};
