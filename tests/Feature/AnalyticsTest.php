<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-27 04:00:00', 'UTC'));
        $user = User::factory()->create();
        $user->is_staff = true;
        $user->save();
        $this->actingAs($user);
    }

    private function ticket(string $created, array $attributes = []): Ticket
    {
        $ticket = Ticket::create($attributes + ['reference' => 'TEST-'.\Illuminate\Support\Str::random(12), 'name' => 'Requester', 'email' => 'requester@example.com', 'department_id' => Department::first()->id, 'category' => 'internet-network', 'subject' => 'Network issue', 'description' => 'The network connection in the office is unavailable.', 'priority' => 'normal', 'status' => 'Open']);
        $ticket->forceFill(['created_at' => CarbonImmutable::parse($created, 'UTC')])->save();
        return $ticket;
    }

    public function test_daily_buckets_use_philippine_midnight_and_include_empty_days(): void
    {
        $this->ticket('2026-10-26 15:59:59'); // October 26, 11:59 PM Manila.
        $this->ticket('2026-10-26 16:00:00'); // October 27, midnight Manila.
        $this->ticket('2026-09-01 00:00:00'); // Outside last 14 days.
        $data = $this->getJson('/dashboard/analytics?period=day')->assertOk()->json();
        $this->assertCount(14, $data['labels']);
        $this->assertSame(2, $data['total']);
        $this->assertSame(1, $data['volume'][12]);
        $this->assertSame(1, $data['volume'][13]);
        $this->assertSame(2, array_sum($data['departments']['data']));
        $this->assertSame(2, array_sum($data['categories'][0]['data']));
    }

    public function test_week_and_month_boundaries_are_grouped_correctly(): void
    {
        $this->ticket('2026-10-25 15:59:59'); // Sunday Manila.
        $this->ticket('2026-10-25 16:00:00'); // Monday Manila.
        $this->ticket('2026-09-30 16:00:00'); // October Manila.
        $week = $this->getJson('/dashboard/analytics?period=week')->assertOk()->json();
        $this->assertCount(12, $week['volume']);
        $this->assertSame(1, $week['volume'][10]);
        $this->assertSame(1, $week['volume'][11]);
        $month = $this->getJson('/dashboard/analytics?period=month')->assertOk()->json();
        $this->assertCount(12, $month['volume']);
        $this->assertSame(3, $month['volume'][11]);
    }

    public function test_combined_filters_apply_to_both_table_and_analytics(): void
    {
        $department = Department::first();
        $this->ticket('2026-10-27 00:00:00', ['subject' => 'Matching network request']);
        $this->ticket('2026-10-27 00:00:00', ['status' => 'Closed', 'subject' => 'Closed request']);
        $this->ticket('2026-10-27 00:00:00', ['category' => 'software', 'subject' => 'Different category']);
        $other = Department::create(['name' => 'Another office']);
        $this->ticket('2026-10-27 00:00:00', ['department_id' => $other->id, 'subject' => 'Different department']);
        $query = http_build_query(['status' => 'Open', 'department_id' => $department->id, 'category' => 'internet-network', 'period' => 'day']);
        $this->getJson('/dashboard/analytics?'.$query)->assertOk()->assertJsonPath('total', 1);
        $response = $this->get('/dashboard?'.$query)->assertOk();
        $this->assertCount(1, $response->viewData('tickets'));
        $this->assertSame('Matching network request', $response->viewData('tickets')->sole()->subject);
        $this->assertSame(['period' => 'day'], $response->viewData('analytics')); // Status pages skip unused analytics work.
        $this->getJson('/dashboard/analytics?period=invalid')->assertUnprocessable();
        $this->getJson('/dashboard/analytics?department_id=999999')->assertUnprocessable();
    }

    public function test_analytics_is_staff_only_and_handles_no_data(): void
    {
        $this->getJson('/dashboard/analytics')->assertOk()->assertJsonPath('total', 0);
        $this->actingAs(User::factory()->create())->getJson('/dashboard/analytics')->assertForbidden();
        $this->post('/logout');
        $this->getJson('/dashboard/analytics')->assertUnauthorized();
    }
}
