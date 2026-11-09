<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        // Starter entries only; the institution's final directory can be added hereafter.
        foreach ([['name' => 'Information Technology Services Unit', 'code' => 'ITSU'], ['name' => 'Other Department / Office', 'code' => 'OTHER']] as $department) {
            DB::table('departments')->insert($department + ['created_at' => now(), 'updated_at' => now()]);
        }
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number')->nullable()->unique();
            $table->string('name', 120);
            $table->string('email')->unique();
            $table->string('phone', 40)->nullable();
            $table->string('position')->nullable();
            $table->string('specialization')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('technician_id');
            $table->dropConstrainedForeignId('department_id');
        });
        Schema::dropIfExists('technicians');
        Schema::dropIfExists('departments');
    }
};
