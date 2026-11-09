<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Technician;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_nonstaff_cannot_access_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertForbidden();
    }

    public function test_staff_can_login_view_dashboard_and_logout(): void
    {
        $staff = $this->staff();
        $this->get('/login')->assertOk()->assertSee('Username or Email');
        $this->post('/login', ['email' => $staff->email, 'password' => 'valid-password-123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($staff);
        $this->get('/dashboard')->assertOk()->assertSee('Support Tickets');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_password_and_nonstaff_login_are_rejected(): void
    {
        $staff = $this->staff();
        $this->post('/login', ['email' => $staff->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $user = User::factory()->create(['password' => 'valid-password-123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'valid-password-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_staff_can_assign_and_update_a_ticket_and_public_tracking_reflects_it(): void
    {
        $ticket = Ticket::create(['reference' => 'APP-2026-TEST12345678', 'name' => 'Test Requester', 'email' => 'requester@example.com', 'category' => 'software', 'subject' => 'Application issue', 'description' => 'Application will not start on the office PC.', 'priority' => 'normal']);
        $technician = Technician::create(['name' => 'Test Technician', 'email' => 'tech@example.com']);
        $this->actingAs($this->staff())->get('/dashboard/tickets/'.$ticket->id)->assertOk()->assertSee('Application issue');
        $this->post('/dashboard/tickets/'.$ticket->id.'/action', ['action_taken' => 'Troubleshooting the reported issue.', 'status' => 'In Progress', 'technician_id' => ''])->assertSessionHasErrors('technician_id');
        $this->post('/dashboard/tickets/'.$ticket->id.'/action', ['action_taken' => 'Troubleshooting the reported issue.', 'status' => 'In Progress', 'technician_id' => $technician->id])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'In Progress', 'technician_id' => $technician->id]);
        $this->post('/track', ['reference' => $ticket->reference, 'email' => $ticket->email])->assertSee('In Progress')->assertSee('Test Technician');
        $this->assertCount(0, $this->get('/dashboard?q=nonexistent')->viewData('tickets'));
    }

    public function test_development_admin_can_sign_in_with_username(): void
    {
        $this->seed(\Database\Seeders\DevUserSeeder::class);
        $this->post('/login', ['email' => 'admin', 'password' => '123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::where('username', 'admin')->firstOrFail());
        $this->get('/dashboard')->assertOk();
    }

    public function test_only_staff_can_delete_tickets_and_their_timeline(): void
    {
        $ticket = Ticket::create(['reference' => 'APP-2026-ITS-000001', 'name' => 'Test Requester', 'email' => 'requester@example.com', 'category' => 'software', 'subject' => 'Application issue', 'description' => 'Application will not start on the office PC.', 'priority' => 'normal']);
        $url = '/dashboard/tickets/'.$ticket->id;
        $this->assertDatabaseHas('ticket_updates', ['ticket_id' => $ticket->id]);
        $this->deleteJson($url)->assertUnauthorized();
        $this->actingAs(User::factory()->create())->deleteJson($url)->assertForbidden();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
        $this->actingAs($this->staff())->getJson('/dashboard/tickets/data')->assertOk()->assertSee('data-ticket-delete-url', false);
        $this->deleteJson($url)->assertOk()->assertJsonPath('message', 'Ticket deleted successfully.');
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('ticket_updates', ['ticket_id' => $ticket->id]);
        $this->deleteJson($url)->assertNotFound();
    }

    private function staff(): User
    {
        $user = User::factory()->create(['password' => 'valid-password-123']);
        $user->is_staff = true;
        $user->save();
        return $user;
    }
}
