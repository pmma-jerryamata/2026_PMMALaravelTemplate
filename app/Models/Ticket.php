<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    public ?string $actionTaken = null;

    protected static function booted(): void
    {
        static::created(function (Ticket $ticket) {
            $ticket->updates()->create(['user_id' => auth()->id(), 'actor_name' => auth()->user()?->name ?? 'Request Submitted', 'event' => 'created', 'summary' => 'Support request submitted.', 'created_at' => $ticket->created_at]);
        });
        static::updated(function (Ticket $ticket) {
            $labels = ['status' => 'Status', 'technician_id' => 'Assigned Technician', 'department_id' => 'Department', 'category' => 'Concern Category', 'priority' => 'Priority', 'subject' => 'Subject', 'description' => 'Concern Details', 'name' => 'Requester Name', 'email' => 'Requester Email'];
            $changes = [];
            foreach ($labels as $field => $label) {
                if (!$ticket->wasChanged($field)) continue;
                $old = $ticket->getRawOriginal($field);
                $new = $ticket->getAttribute($field);
                if (in_array($field, ['name', 'email', 'subject', 'description'])) {
                    $changes[$label] = ['from' => 'Previous value', 'to' => 'Updated'];
                    continue;
                }
                $format = fn ($value) => match ($field) {
                    'technician_id' => Technician::find($value)?->name ?? 'Unassigned',
                    'department_id' => Department::find($value)?->name ?? 'Not Specified',
                    'category' => ServiceCategory::label((string) $value),
                    default => ucfirst((string) $value),
                };
                $changes[$label] = ['from' => $format($old), 'to' => $format($new)];
            }
            if (!$changes && !$ticket->actionTaken) return;
            $ticket->updates()->create(['user_id' => auth()->id(), 'actor_name' => auth()->user()?->name ?? 'System', 'event' => $ticket->actionTaken ? 'action' : 'updated', 'summary' => $changes ? 'Updated: '.implode(', ', array_keys($changes)).'.' : 'Technician action recorded.', 'action_taken' => $ticket->actionTaken, 'changes' => $changes]);
            $ticket->actionTaken = null;
        });
    }

    public function updates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketUpdate::class)->orderBy('created_at')->orderBy('id');
    }
    public function latestAction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TicketUpdate::class)->ofMany(['id' => 'max'], fn ($query) => $query->whereNotNull('action_taken')->where('action_taken', '<>', ''));
    }
    public function latestUpdate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TicketUpdate::class)->latestOfMany();
    }
    protected $fillable = ['reference', 'name', 'email', 'category', 'subject', 'description', 'priority', 'status', 'department_id', 'technician_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
