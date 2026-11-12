<?php

namespace Tests\Feature;

use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_pages_render_and_unknown_services_return_not_found(): void
    {
        foreach (['/', '/services', '/services/internet-network', '/tickets/create', '/track', '/faq', '/contact', '/privacy'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/services/unknown')->assertNotFound();
        $this->get('/tickets/receipt')->assertNotFound();
        $this->get('/services?q=connectivity')->assertSee('Internet/Network Connectivity', false)->assertDontSee('PC/Laptop Troubleshooting', false);
        $this->get('/services?q=zzzzzz')->assertSee('No Services Found');
    }

    public function test_ticket_is_persisted_and_receipt_is_available_to_submitter(): void
    {
        $response = $this->post('/tickets', $this->validTicket());
        $response->assertRedirect(route('tickets.receipt'));
        $ticket = Ticket::sole();
        $this->assertSame($this->validTicket()['description'], $ticket->description);
        $this->assertSame($ticket->description, $ticket->subject);
        $this->assertSame('Open', $ticket->status);
        $this->assertSame('APP-'.now()->format('Y').'-ITS-000001', $ticket->reference);
        $this->get('/tickets/receipt')->assertOk()->assertSee($ticket->reference);
    }

    public function test_receipt_survives_intervening_requests_and_refreshes(): void
    {
        $this->post('/tickets', $this->validTicket())->assertRedirect(route('tickets.receipt'));
        $reference = Ticket::sole()->reference;
        // Background polling or navigation must not expire the saved receipt.
        $this->get('/faq')->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/tickets/receipt')->assertOk()->assertSee($reference);
        $this->get('/tickets/receipt')->assertOk()->assertSee($reference);
        $this->flushSession();
        $this->get('/tickets/receipt')->assertNotFound();
    }

    public function test_detailed_concern_is_preserved_and_subject_fits_ticket_lists(): void
    {
        $description = "Library network is unavailable.\n\n".str_repeat('Connection drops repeatedly. ', 20);
        $this->post('/tickets', array_replace($this->validTicket(), ['description' => $description]))
            ->assertRedirect(route('tickets.receipt'));
        $ticket = Ticket::sole();
        $this->assertSame(trim($description), $ticket->description);
        $this->assertLessThanOrEqual(200, mb_strlen($ticket->subject));
        $this->assertStringNotContainsString("\n", $ticket->subject);
        $this->get('/tickets/create')->assertDontSee('name="subject"', false)->assertSee('Describe Your Concern');
    }

    public function test_ticket_numbers_increment_per_office_and_year(): void
    {
        $data = $this->validTicket();
        $this->post('/tickets', array_replace($data, ['submission_token' => (string) \Illuminate\Support\Str::uuid()]))->assertRedirect(route('tickets.receipt'));
        $this->post('/tickets', array_replace($data, ['submission_token' => (string) \Illuminate\Support\Str::uuid()]))->assertRedirect(route('tickets.receipt'));
        $students = \App\Models\Department::where('name', 'Students')->value('id');
        $this->post('/tickets', array_replace($data, ['department_id' => $students, 'submission_token' => (string) \Illuminate\Support\Str::uuid()]))->assertRedirect(route('tickets.receipt'));
        $year = now()->format('Y');
        $this->assertSame(["APP-$year-ITS-000001", "APP-$year-ITS-000002", "APP-$year-STU-000001"], Ticket::orderBy('id')->pluck('reference')->all());
        $this->travel(1)->years();
        $this->post('/tickets', array_replace($data, ['submission_token' => (string) \Illuminate\Support\Str::uuid()]))->assertRedirect(route('tickets.receipt'));
        $this->assertSame('APP-'.now()->format('Y').'-ITS-000001', Ticket::latest('id')->first()->reference);
    }

    public function test_invalid_ticket_is_rejected(): void
    {
        $this->post('/tickets', array_replace($this->validTicket(), ['email' => 'invalid', 'category' => 'unknown', 'description' => 'short', 'consent' => '0']))
            ->assertSessionHasErrors(['email', 'category', 'description', 'consent']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_tracking_requires_only_the_reference_number(): void
    {
        $this->post('/tickets', $this->validTicket());
        $ticket = Ticket::sole();
        $this->post('/track', ['reference' => 'APP-UNKNOWN'])
            ->assertOk()->assertSee('We could not find a matching ticket.')->assertDontSee('The WiFi connection in the library has been unavailable since this morning.');
        $this->post('/track', ['reference' => strtolower($ticket->reference)])
            ->assertOk()->assertSee('The WiFi connection in the library has been unavailable since this morning.')->assertSee('Open');
        $this->get('/track')->assertDontSee('The WiFi connection in the library has been unavailable since this morning.');
    }

    public function test_department_must_exist_and_be_active(): void
    {
        $data = $this->validTicket();
        unset($data['department_id']);
        $this->post('/tickets', $data)->assertSessionHasErrors('department_id');
        $this->post('/tickets', $data + ['department_id' => 999999])->assertSessionHasErrors('department_id');
        $department = \App\Models\Department::create(['name' => 'Inactive test office', 'is_active' => false]);
        $this->post('/tickets', $data + ['department_id' => $department->id])->assertSessionHasErrors('department_id');
        $this->get('/tickets/create')->assertDontSee('Inactive test office');
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_technician_profile_can_be_assigned_to_a_ticket(): void
    {
        $this->post('/tickets', $this->validTicket());
        $ticket = Ticket::sole();
        $technician = \App\Models\Technician::create([
            'department_id' => $ticket->department_id,
            'name' => 'Test Technician', 'email' => 'technician@example.com',
            'position' => 'IT Technician', 'specialization' => 'Networking',
            'bio' => 'Test profile',
        ]);
        $ticket->update(['technician_id' => $technician->id]);
        $this->assertSame($technician->id, $ticket->fresh()->technician->id);
        $this->assertSame($ticket->id, $technician->tickets->sole()->id);
        $this->post('/track', ['reference' => $ticket->reference, 'email' => $ticket->email])
            ->assertSee('Test Technician')->assertSee('ITSU: Info. Technology Services Unit');
    }

    private function validTicket(): array
    {
        return ['submission_token' => (string) \Illuminate\Support\Str::uuid(), 'department_id' => \App\Models\Department::where('code', 'ITSU')->value('id'), 'name' => 'Test Cadet', 'email' => 'cadet@example.com', 'category' => 'internet-network', 'description' => 'The WiFi connection in the library has been unavailable since this morning.', 'priority' => 'normal', 'consent' => '1'];
    }
}
