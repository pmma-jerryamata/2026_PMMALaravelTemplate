<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_reference_counters', function (Blueprint $table) {
            $table->unsignedSmallInteger('year');
            $table->string('office', 3);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->primary(['year', 'office']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_reference_counters');
    }
};
