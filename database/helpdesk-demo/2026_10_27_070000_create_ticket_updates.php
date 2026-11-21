<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name');
            $table->string('event', 40);
            $table->text('summary');
            $table->text('action_taken')->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['ticket_id', 'created_at']);
        });
        DB::table('tickets')->orderBy('id')->chunkById(100, function ($tickets) {
            foreach ($tickets as $ticket) {
                DB::table('ticket_updates')->insert(['ticket_id' => $ticket->id, 'actor_name' => 'Request Submitted', 'event' => 'created', 'summary' => 'Support request submitted.', 'created_at' => $ticket->created_at]);
                if ($ticket->updated_at > $ticket->created_at) DB::table('ticket_updates')->insert(['ticket_id' => $ticket->id, 'actor_name' => 'System', 'event' => 'imported', 'summary' => 'Existing ticket imported into timeline. Current status: '.$ticket->status.'. Earlier action details were not recorded.', 'created_at' => $ticket->updated_at]);
            }
        });
    }
    public function down(): void { Schema::dropIfExists('ticket_updates'); }
};
