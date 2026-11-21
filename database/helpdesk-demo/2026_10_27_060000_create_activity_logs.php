<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->boolean('is_dev_admin')->default(false));
        DB::table('users')->where('username', 'admin')->where('email', 'admin@example.test')->update(['is_dev_admin' => true]);
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name')->index();
            $table->string('actor_login')->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('operation', 120)->index();
            $table->string('outcome', 20)->index();
            $table->string('method', 10);
            $table->string('path', 500);
            $table->unsignedSmallInteger('status_code');
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_dev_admin'));
    }
};
