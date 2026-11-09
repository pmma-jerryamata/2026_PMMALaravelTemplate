<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Technician;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTimelineTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        $user = User::factory()->create(['name' => 'Support Technician']);
        $user->is_staff = true;
        $user->save();
        return $user;
    }

    private function ticket(): Ticket
    {
        return Ticket::create(['reference' => 'APP-2026-ABC123XYZ789', 'name' => 'Private Requester', 'email' => 'private@example.com', 'department_id' => Department::first()->id, 'category' => 'internet-network', 'subject' => 'Network unavailable', 'description' => 'The office network connection has stopped working.', 'priority' => 'normal']);
    }

    public function test_creation_and_every_staff_action_appear_on_public_tracking_without_email(): void
    {
        $ticket = $this->ticket();
        $technician = Technician::create(['name' => 'Assigned Technician', 'email' => 'assigned@example.com']);
        $this->assertCount(1, $ticket->updates);
        $this->actingAs($this->staff())->postJson('/dashboard/tickets/'.$ticket->id.'/action', ['status' => 'In Progress', 'technician_id' => $technician->id, 'action_taken' => 'Inspected the network cable and restarted the switch.'])->assertOk();
        $this->postJson('/dashboard/tickets/'.$ticket->id.'/action', ['status' => 'In Progress', 'technician_id' => $technician->id, 'action_taken' => 'Monitoring the connection for stability.'])->assertOk();
        $this->postJson('/dashboard/tickets/'.$ticket->id.'/action', ['status' => 'Resolved', 'technician_id' => $technician->id, 'action_taken' => 'Connection restored and verified with the requester.'])->assertOk();
        $updates = $ticket->fresh()->updates;
        $this->assertCount(4, $updates); // No duplicate entries for note-only actions.
        $this->assertSame('Open', $updates[1]->changes['Status']['from']);
        $this->assertSame('In Progress', $updates[1]->changes['Status']['to']);
        $this->assertSame('Assigned Technician', $updates[1]->changes['Assigned Technician']['to']);
        $this->post('/logout');
        $this->post('/track', ['reference' => strtolower($ticket->reference)])->assertOk()->assertSee('Ticket Timeline')->assertSee('Inspected the network cable')->assertSee('Monitoring the connection')->assertSee('Connection restored')->assertSee('Resolved')->assertDontSee('private@example.com');
        $this->get('/track')->assertDontSee('id="track-email"', false);
    }

    public function test_details_editing_is_separate_and_records_changes_without_exposing_private_values(): void
    {
        $ticket = $this->ticket();
        $this->actingAs($this->staff())->patchJson('/dashboard/tickets/'.$ticket->id, ['status' => 'Resolved'])->assertUnprocessable();
        $this->patchJson('/dashboard/tickets/'.$ticket->id, ['subject' => 'Updated network concern', 'email' => 'new-private@example.com'])->assertOk();
        $update = $ticket->fresh()->updates->last();
        $this->assertStringContainsString('Subject', $update->summary);
        $this->assertStringNotContainsString('new-private@example.com', $update->toJson());
        $this->postJson('/dashboard/tickets/'.$ticket->id.'/action', ['status' => 'Open'])->assertUnprocessable()->assertJsonValidationErrors('action_taken');
        $this->getJson('/dashboard/tickets/'.$ticket->id.'/timeline')->assertOk()->assertJsonPath('reference', $ticket->reference);
        $this->post('/logout');
        $this->getJson('/dashboard/tickets/'.$ticket->id.'/timeline')->assertUnauthorized();
    }

    public function test_closing_a_ticket_does_not_require_action_taken(): void
    {
        $ticket = $this->ticket();
        $url = '/dashboard/tickets/'.$ticket->id.'/action';
        $this->actingAs($this->staff())->postJson($url, ['status' => 'Closed'])->assertOk();
        $this->assertSame('Closed', $ticket->fresh()->status);
        $update = $ticket->fresh()->updates->last();
        $this->assertSame('Closed', $update->changes['Status']['to']);
        $this->assertNull($update->action_taken);
        $this->postJson($url, ['status' => 'Closed', 'action_taken' => ''])->assertOk();
        $this->assertCount(2, $ticket->fresh()->updates);
        $this->postJson($url, ['status' => 'Resolved'])->assertUnprocessable()->assertJsonValidationErrors('action_taken');
    }

    public function test_timeline_displays_latest_updates_first_including_matching_timestamps(): void
    {
        $ticket = $this->ticket();
        $time = now()->addMinute();
        $ticket->updates()->create(['event' => 'action', 'summary' => 'Earlier technician update', 'actor_name' => 'Technician', 'created_at' => $time]);
        $ticket->updates()->create(['event' => 'action', 'summary' => 'Most recent technician update', 'actor_name' => 'Technician', 'created_at' => $time]);
        $html = view('portal.partials.timeline', ['updates' => $ticket->fresh()->updates])->render();
        $this->assertLessThan(strpos($html, 'Earlier technician update'), strpos($html, 'Most recent technician update'));
        $this->assertLessThan(strpos($html, 'Support request submitted.'), strpos($html, 'Earlier technician update'));
    }

    public function test_recent_tickets_include_older_requests_newest_first_and_are_public_view_only(): void
    {
        $this->travelTo(now()->startOfSecond());
        $recent = $this->ticket();
        $recent->forceFill(['status' => 'Resolved', 'created_at' => now()->subHours(23)])->save();
        $old = Ticket::create(['reference' => 'APP-2026-OLD123456789', 'name' => 'Old Requester', 'email' => 'old@example.com', 'category' => 'software', 'subject' => 'Old request', 'description' => 'This ticket is older than one day.', 'priority' => 'normal']);
        $old->forceFill(['created_at' => now()->subHours(25)])->save();
        $response = $this->get('/')->assertOk()->assertDontSee('private@example.com')->assertDontSee('Private Requester');
        $this->assertSame([$recent->id, $old->id], $response->viewData('recentTickets')->pluck('id')->all());
        $this->getJson('/tickets/recent/data')->assertOk()->assertJsonPath('recordsTotal', 2)
            ->assertSee('Old request')->assertDontSee('old@example.com');
        $response->assertSee('Recent Tickets')->assertDontSee('Last 24 Hours');
        $html = view('portal.partials.recent-tickets', ['recentTickets' => $response->viewData('recentTickets')])->render();
        $this->assertStringContainsString('View Timeline', $html);
        $this->assertStringNotContainsString('data-ticket-url', $html);
        $this->assertStringNotContainsString('data-ticket-action-url', $html);
        $this->getJson('/tickets/'.$recent->reference.'/timeline')->assertOk()->assertJsonPath('reference', $recent->reference)->assertDontSee('private@example.com');
        $this->getJson('/tickets/unknown/timeline')->assertNotFound();
        $this->actingAs($this->staff())->get('/dashboard?status=Open')->assertOk()->assertDontSee('Support Tickets — Last 24 Hours')->assertDontSee('data-public-datatable', false);
    }

    public function test_public_and_staff_tables_show_latest_action_and_current_status_safely(): void
    {
        $ticket = $this->ticket();
        $this->get('/')->assertOk()->assertSee('Last Action Taken')->assertSee('Awaiting technician action.');
        $ticket->updates()->create(['event' => 'action', 'summary' => 'Technician action recorded.', 'action_taken' => 'Older action', 'actor_name' => 'Technician']);
        $ticket->updates()->create(['event' => 'action', 'summary' => 'Technician action recorded.', 'action_taken' => 'Latest action <script>alert(1)</script>', 'actor_name' => 'Technician']);
        $ticket->update(['status' => 'Resolved']); // A later status update must not hide the last actual action.
        $this->get('/')->assertOk()->assertSee('Resolved')->assertSee('Latest action')->assertDontSee('<script>alert(1)</script>', false);
        $public = $this->getJson('/tickets/recent/data')->assertOk()->json('rows');
        $this->assertStringContainsString('Resolved', $public);
        $this->assertStringContainsString('Latest action &lt;script&gt;', $public);
        $this->assertStringNotContainsString('Older action', $public);
        $this->assertStringNotContainsString('private@example.com', $public);
        $this->actingAs($this->staff())->get('/dashboard?status=Resolved')->assertOk()->assertSee('Last Action Taken')->assertSee('Latest action');
        $staff = $this->getJson('/dashboard/tickets/data')->assertOk()->json('rows');
        $this->assertStringContainsString('Latest action &lt;script&gt;', $staff);
    }
}
