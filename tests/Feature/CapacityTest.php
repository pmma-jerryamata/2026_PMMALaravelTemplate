<?php

namespace Tests\Feature;

use App\Http\Middleware\LimitDatabaseConcurrency;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_independent_php_workers_share_the_same_capacity_limit(): void
    {
        $directory = storage_path('framework/testing-capacity-'.uniqid()); mkdir($directory);
        $workers = [];
        try {
            for ($i = 0; $i < 6; $i++) {
                $worker = new \Symfony\Component\Process\Process([PHP_BINARY, base_path('tests/Support/capacity-worker.php'), $directory]);
                $worker->setTimeout(25)->start(); $workers[] = $worker;
            }
            $deadline = microtime(true) + 15;
            while (count(glob($directory.'/ready-*')) < 6 && microtime(true) < $deadline) usleep(10000);
            $this->assertCount(6, glob($directory.'/ready-*'));
            touch($directory.'/go');
            $results = [];
            foreach ($workers as $worker) { $worker->wait(); $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput()); $results[] = trim($worker->getOutput()); }
            $this->assertSame(2, count(array_filter($results, fn ($code) => $code === '200')), json_encode($results));
            $this->assertSame(4, count(array_filter($results, fn ($code) => $code === '503')));
        } finally {
            foreach ($workers as $worker) if ($worker->isRunning()) $worker->stop();
            foreach (glob($directory.'/*') as $file) unlink($file);
            rmdir($directory);
        }
    }

    public function test_full_capacity_rejects_before_database_work_and_releases_after_termination(): void
    {
        $directory = storage_path('framework/testing-capacity-'.uniqid());
        config(['capacity.enabled' => true, 'capacity.requests' => 1, 'capacity.directory' => $directory]);
        $middleware = new LimitDatabaseConcurrency;
        $request = Request::create('/tickets', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $accepted = $middleware->handle($request, fn () => response('accepted'));
        $this->assertSame(200, $accepted->getStatusCode());
        $called = false;
        $busy = $middleware->handle($request, function () use (&$called) { $called = true; return response('unexpected'); });
        $this->assertFalse($called);
        $this->assertSame(503, $busy->getStatusCode());
        $this->assertSame('5', $busy->headers->get('Retry-After'));
        $this->app->terminate();
        $this->assertSame(200, $middleware->handle($request, fn () => response('accepted'))->getStatusCode());
        $this->app->terminate();
        unlink($directory.'/slot-0'); rmdir($directory);
    }

    public function test_database_connection_exhaustion_has_standalone_busy_response(): void
    {
        Route::get('/capacity-test', function () {
            $error = new \PDOException('SQLSTATE: Too many connections');
            $error->errorInfo = ['HY000', 1040, 'Too many connections'];
            throw $error;
        });
        $this->getJson('/capacity-test')->assertStatus(503)->assertHeader('Retry-After', '5')->assertJsonStructure(['message'])->assertDontSee('SQLSTATE');
        $this->get('/capacity-test')->assertStatus(503)->assertSee('Helpdesk Temporarily Busy')->assertDontSee('SQLSTATE');
    }

    public function test_ticket_tables_bound_page_size_filter_and_exclude_private_data(): void
    {
        for ($i = 0; $i < 16; $i++) Ticket::create([
            'reference' => 'APP-LOAD-'.$i, 'name' => 'Private Requester', 'email' => 'private@example.com',
            'category' => 'internet-network', 'subject' => $i === 15 ? 'Unique concern' : 'Network issue',
            'description' => 'Network issue in the office.', 'priority' => 'normal',
        ]);
        $this->assertCount(10, $this->get('/')->assertOk()->viewData('recentTickets'));
        $page = $this->getJson('/tickets/recent/data?draw=2&start=10&length=10')->assertOk()->assertJsonPath('draw', 2)->assertJsonPath('recordsTotal', 16)->assertDontSee('private@example.com')->assertDontSee('Private Requester');
        $this->assertSame(6, substr_count($page->json('rows'), '<tr>'));
        $this->getJson('/tickets/recent/data?search[value]=Unique')->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->getJson('/tickets/recent/data?length=100000')->assertUnprocessable();
        $this->getJson('/dashboard/tickets/data')->assertUnauthorized();
        $user = User::factory()->create(); $user->forceFill(['is_staff' => true])->save();
        $this->actingAs($user)->getJson('/dashboard/tickets/data?status=Resolved')->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->assertCount(10, $this->get('/dashboard?status=Open')->assertOk()->viewData('tickets'));
    }

    public function test_timeline_failure_rolls_back_ticket_creation(): void
    {
        \App\Models\TicketUpdate::creating(function () { throw new \RuntimeException('Simulated history failure'); });
        try {
            $this->post('/tickets', ['name' => 'Test Requester', 'email' => 'test@example.com', 'department_id' => \App\Models\Department::first()->id,
                'category' => 'internet-network', 'subject' => 'Connectivity issue', 'description' => 'The office network is unavailable.', 'priority' => 'normal', 'consent' => '1'])->assertStatus(500);
            $this->assertDatabaseCount('tickets', 0);
        } finally { \App\Models\TicketUpdate::flushEventListeners(); }
    }
}
