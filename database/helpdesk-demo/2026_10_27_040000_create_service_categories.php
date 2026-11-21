<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('icon')->default('life-preserver');
            $table->text('description');
            $table->json('items');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        foreach (config('services_catalog') as $slug => $service) {
            DB::table('service_categories')->insert(['slug' => $slug] + array_replace($service, ['items' => json_encode($service['items'])]) + ['created_at' => now(), 'updated_at' => now()]);
        }
    }
    public function down(): void { Schema::dropIfExists('service_categories'); }
};
