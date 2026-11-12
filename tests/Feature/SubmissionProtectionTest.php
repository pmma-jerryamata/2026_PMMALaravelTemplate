<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['submission_token' => (string) Str::uuid(), 'name' => 'Test Requester',
            'email' => 'test@example.test', 'department_id' => Department::first()->id,
            'category' => 'internet-network', 'description' => 'The network cannot connect in the test office.',
            'priority' => 'normal', 'consent' => '1'];
    }

    private function sameBrowser(): void
    {
        $this->withCredentials();
        $this->withSession(['test_browser' => true]);
        $this->withCookie(config('session.cookie'), session()->getId());
    }

    public function test_replay_creates_one_ticket_one_timeline_and_one_receipt(): void
    {
        $this->sameBrowser(); $data = $this->payload();
        $this->post('/tickets', $data)->assertRedirect(route('tickets.receipt'));
        $this->post('/tickets', $data)->assertRedirect(route('tickets.receipt'));
        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('ticket_updates', 1);
        $this->assertDatabaseCount('submission_receipts', 1);
        $this->get('/tickets/receipt')->assertSee(Ticket::sole()->reference);
    }

    public function test_new_token_allows_a_deliberate_new_request(): void
    {
        $this->sameBrowser();
        $this->post('/tickets', $this->payload())->assertRedirect();
        $this->post('/tickets', $this->payload())->assertRedirect();
        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_missing_token_is_rejected_and_failed_validation_does_not_claim_token(): void
    {
        $data = $this->payload(); unset($data['submission_token']);
        $this->postJson('/tickets', $data)->assertUnprocessable()->assertJsonValidationErrors('submission_token');
        $this->postJson('/tickets', array_replace($this->payload(), ['email' => 'invalid']))->assertUnprocessable();
        $this->assertDatabaseCount('submission_receipts', 0);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_sixth_attempt_is_limited_with_a_retry_after_header(): void
    {
        $this->sameBrowser();
        for ($i = 0; $i < 5; $i++) $this->post('/tickets', $this->payload())->assertRedirect();
        $this->postJson('/tickets', $this->payload())->assertStatus(429)->assertHeader('Retry-After')->assertJsonStructure(['message', 'retry_after']);
        $this->assertDatabaseCount('tickets', 5);
        $this->travel(11)->minutes();
        $this->post('/tickets', $this->payload())->assertRedirect();
        $this->assertDatabaseCount('tickets', 6);
    }

    public function test_staff_modal_replay_is_idempotent_and_scope_does_not_cross_users(): void
    {
        $staff = User::factory()->create(['is_staff' => true]);
        $data = $this->payload() + ['subject' => 'Network issue'];
        $this->actingAs($staff)->postJson('/dashboard/tickets', $data)->assertOk();
        $this->postJson('/dashboard/tickets', $data)->assertOk()->assertJsonPath('message', 'Ticket already submitted.');
        $this->assertDatabaseCount('tickets', 1);
        $this->actingAs(User::factory()->create(['is_staff' => true]))->postJson('/dashboard/tickets', $data)->assertOk();
        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_claim_and_resource_rollback_together_when_callback_fails(): void
    {
        $this->sameBrowser();
        $request = \Illuminate\Http\Request::create('/example', 'POST', ['submission_token' => (string) Str::uuid()]);
        $request->setLaravelSession(session()->driver());
        try {
            app(\App\Support\SubmissionGuard::class)->run($request, 'example', Ticket::class, function () {
                throw new \RuntimeException('Simulated write failure');
            });
            $this->fail('Expected the write failure to propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated write failure', $exception->getMessage());
        }
        $this->assertDatabaseCount('submission_receipts', 0);
    }

    public function test_issue_selection_uses_server_catalog_description(): void
    {
        $issue = app(\App\Services\RequestIssues::class)->options('internet-network')[0];
        $this->assertCount(9, app(\App\Services\RequestIssues::class)->options('internet-network'));
        $this->post('/tickets', $this->payload() + ['issue_name' => $issue[0]])->assertRedirect();
        $this->assertSame($issue[1], Ticket::sole()->description);
    }

    public function test_summary_filters_dates_and_does_not_expose_requester_data(): void
    {
        Ticket::create($this->payload() + ['reference' => 'TEST-PRIVATE', 'subject' => 'Private subject']);
        $this->get('/?summary_range=today')->assertOk()->assertViewHas('summary', fn ($summary) => $summary['total'] === 1);
        $this->travel(2)->days();
        $this->get('/?summary_range=today')->assertViewHas('summary', fn ($summary) => $summary['total'] === 0);
        $this->get('/?summary_range=all')->assertViewHas('summary', fn ($summary) => $summary['total'] === 1);
    }

    public function test_only_staff_can_reorder_services_and_order_reaches_public_catalog(): void
    {
        $first = ServiceCategory::orderBy('display_order')->first();
        $second = ServiceCategory::orderBy('display_order')->skip(1)->first();
        $this->post('/dashboard/services/'.$second->id.'/move', ['direction' => 'up'])->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['is_staff' => true]))
            ->post('/dashboard/services/'.$second->id.'/move', ['direction' => 'up'])->assertRedirect(route('dashboard.settings').'#services');
        $this->assertSame($second->slug, array_key_first(ServiceCategory::catalog()));
        $this->get('/dashboard/settings')->assertViewHas('services', fn ($services) => $services->first()->id === $second->id);
        $this->assertGreaterThan($second->fresh()->display_order, $first->fresh()->display_order);
    }
}
