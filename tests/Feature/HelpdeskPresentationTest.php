<?php

namespace Tests\Feature;

use App\Mail\Mailer;
use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Tests use SQLite memory or an explicitly isolated MySQL test database, never application data. */
class HelpdeskPresentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array',
        ]);
        DB::purge('sqlite');
        $testDatabase = getenv('HELPDESK_TEST_DATABASE');
        if ($testDatabase) {
            if (! preg_match('/^pmma_helpdesk_test_[a-f0-9]{12}$/', $testDatabase)) {
                throw new \RuntimeException('Refusing to use a non-isolated test database.');
            }
            config(['database.default' => 'helpdesk_test', 'database.connections.helpdesk_test' => [
                'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306,
                'database' => $testDatabase, 'username' => getenv('HELPDESK_TEST_USER'),
                'password' => getenv('HELPDESK_TEST_PASSWORD'), 'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true,
            ]]);
            DB::purge('helpdesk_test');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach (DB::select('SHOW TABLES') as $table) {
                $name = array_values((array) $table)[0];
                if (! preg_match('/^[a-z_]+$/', $name)) { throw new \RuntimeException('Unexpected test table.'); }
                DB::statement('DROP TABLE `'.$name.'`');
            }
            $schema = json_decode(file_get_contents(getenv('HELPDESK_TEST_SCHEMA')), true, 512, JSON_THROW_ON_ERROR);
            foreach ($schema as $statement) { DB::unprepared($statement); }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
        Schema::create('departments', function (Blueprint $table) { $table->id(); $table->string('name'); });
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->bigInteger('department_id');
            $table->softDeletes(); $table->timestamp('updated_at')->nullable();
        });
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('content'); $table->string('icon');
        });
        Schema::create('technicians', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('email'); });
        Schema::create('supports', function (Blueprint $table) {
            $table->id(); $table->bigInteger('employee_id'); $table->bigInteger('task_id');
            $table->bigInteger('technician_id')->nullable(); $table->string('status');
            $table->text('support_specifics')->nullable(); $table->string('findings', 1023)->nullable();
            $table->string('ip'); $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('email'); $table->string('password');
            $table->timestamp('email_verified_at')->nullable(); $table->rememberToken(); $table->timestamps();
        });
        Schema::create('ratings', function (Blueprint $table) { $table->id(); $table->string('name'); });
        Schema::create('ratings_measure', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('description'); });
        Schema::create('ratings_result', function (Blueprint $table) {
            $table->id(); $table->bigInteger('support_id'); $table->bigInteger('rating_id');
            $table->bigInteger('rating_measure_id'); $table->string('feedback')->nullable(); $table->timestamps();
        });
        Schema::create('password_resets', function (Blueprint $table) { $table->string('email')->index(); $table->string('token'); $table->timestamp('created_at')->nullable(); });
        }
        if (! Schema::hasColumn('employees', 'activation_pending')) {
            (require base_path('database/migrations/2026_11_11_000001_add_public_ticket_flow.php'))->up();
        }
        DB::table('departments')->insert([['id' => 1, 'name' => 'ITSU'], ['id' => 44, 'name' => 'STUDENTS']]);
        DB::table('employees')->insert(['id' => 1, 'name' => 'Test Requester', 'email' => 'requester@pmma.edu.ph', 'department_id' => 1]);
        DB::table('tasks')->insert(['id' => 1, 'name' => 'Internet/Network Connectivity', 'content' => 'Network assistance', 'icon' => 'bi bi-wifi']);
        DB::table('technicians')->insert(['id' => 1, 'name' => 'Test Technician', 'email' => 'technician@example.test']);
        DB::table('ratings')->insert(['id' => 1, 'name' => 'Very Satisfied']);
        DB::table('ratings_measure')->insert(['id' => 1, 'name' => 'Responsiveness', 'description' => 'Support responsiveness']);
        Mail::fake();
    }

    private function staff(): User
    {
        return User::create(['name' => 'Test Staff', 'email' => 'staff@example.test', 'password' => bcrypt('test-password')]);
    }

    private function ticket(): Support
    {
        return Support::create(['employee_id' => 1, 'task_id' => 1, 'status' => 'Pending',
            'support_specifics' => 'The network is unavailable in the office.', 'ip' => '127.0.0.1']);
    }

    public function test_ticket_deletion_requires_authentication_and_matching_confirmation(): void
    {
        $ticket = $this->ticket();
        $url = '/dashboard/tickets/'.$ticket->id;
        $this->get($url.'/delete')->assertRedirect('/login');
        $this->deleteJson($url)->assertUnauthorized();
        $this->actingAs($this->staff());
        $this->get($url.'/delete')->assertOk()->assertSee('DELETE #'.$ticket->id)
            ->assertSee('name="password"', false);
        $this->deleteJson($url, ['confirmation' => 'DELETE #999999', 'password' => 'test-password', 'acknowledge' => '1'])
            ->assertUnprocessable()->assertJsonValidationErrors('confirmation');
        $this->deleteJson($url, ['confirmation' => 'DELETE #'.$ticket->id, 'password' => 'wrong', 'acknowledge' => '1'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->deleteJson($url, ['confirmation' => 'DELETE #'.$ticket->id, 'password' => 'test-password'])
            ->assertUnprocessable()->assertJsonValidationErrors('acknowledge');
        $this->assertDatabaseHas('supports', ['id' => $ticket->id]);
    }

    public function test_ticket_deletion_removes_only_the_confirmed_ticket_and_its_feedback(): void
    {
        $ticket = $this->ticket();
        $other = $this->ticket();
        foreach ([$ticket, $other] as $record) {
            DB::table('ratings_result')->insert(['support_id' => $record->id, 'rating_id' => 1,
                'rating_measure_id' => 1, 'feedback' => 'Test feedback', 'created_at' => now()]);
        }
        $this->actingAs($this->staff());
        $this->get('/dashboard')->assertOk()->assertSee('aria-label="Delete ticket '.$ticket->id.'"', false);
        $this->getJson('/dashboard/tables/tickets')->assertOk()
            ->assertJson(fn ($json) => $json->has('rows')->etc());
        $this->delete('/dashboard/tickets/'.$ticket->id, [
            'confirmation' => 'DELETE #'.$ticket->id, 'password' => 'test-password', 'acknowledge' => '1',
        ])->assertRedirect('/dashboard')->assertSessionHas('success');
        $this->assertDatabaseMissing('supports', ['id' => $ticket->id]);
        $this->assertDatabaseMissing('ratings_result', ['support_id' => $ticket->id]);
        $this->assertDatabaseHas('supports', ['id' => $other->id]);
        $this->assertDatabaseHas('ratings_result', ['support_id' => $other->id]);
        $this->assertDatabaseCount('employees', 1);
        $this->assertDatabaseCount('tasks', 1);
        $this->get('/dashboard/tickets/'.$ticket->id.'/delete')->assertNotFound();
    }

    public function test_analytics_counts_departments_and_filters_graphs_without_changing_the_ticket_list(): void
    {
        $old = $this->ticket(); $old->update(['created_at' => '2026-11-01 12:00:00']);
        $completed = $this->ticket(); $completed->update(['status' => 'Completed', 'technician_id' => 1, 'created_at' => '2026-11-09 23:59:59']);
        DB::table('employees')->where('id', 1)->update(['deleted_at' => now()]);
        DB::table('employees')->insert(['id' => 2, 'name' => 'Student Requester', 'email' => 'student@example.test', 'department_id' => 44]);
        Support::create(['employee_id' => 2, 'task_id' => 1, 'status' => 'Cancelled', 'ip' => '127.0.0.1', 'created_at' => '2026-11-09 00:00:00']);
        Support::create(['employee_id' => 999, 'task_id' => 999, 'status' => 'Pending', 'ip' => '127.0.0.1', 'created_at' => '2026-11-09 15:00:00']);
        $this->actingAs($this->staff());
        $all = $this->get('/dashboard')->assertOk()->assertSee('department-chart')->viewData('analytics');
        $this->assertSame(4, $all['total']);
        $this->assertSame(4, $all['categoryCounts']->sum());
        $this->assertSame(4, $all['departmentCounts']->sum());
        $this->assertSame(4, array_sum($all['statusCounts']));
        $this->assertSame(['ITSU', 'Not specified', 'STUDENTS'], $all['departments']->all());
        $this->assertSame([2, 1, 1], $all['departmentCounts']->all());
        $this->assertContains('Unavailable service', $all['categories']->all());
        $query = http_build_query(['analytics_period' => 'custom', 'analytics_from' => '2026-11-09', 'analytics_to' => '2026-11-09',
            'analytics_status' => 'Completed', 'analytics_department_id' => 1, 'analytics_task_id' => 1, 'analytics_technician_id' => 1]);
        $response = $this->get('/dashboard?'.$query)->assertOk();
        $filtered = $response->viewData('analytics');
        $this->assertSame(1, $filtered['total']);
        $this->assertSame(['ITSU'], $filtered['departments']->all());
        $this->assertSame([0, 1, 0], $filtered['statusCounts']);
        $this->assertSame(4, $response->viewData('tickets')->total());
        $this->assertEquals(4, $response->viewData('counts')->sum());
    }

    public function test_analytics_periods_empty_results_and_filter_validation(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-09 12:00:00', 'Asia/Manila'));
        $ticket = $this->ticket(); $ticket->update(['created_at' => '2026-11-03 00:00:00']);
        $this->actingAs($this->staff());
        $this->get('/dashboard?analytics_period=7days')->assertOk()->assertViewHas('analytics', fn ($data) => $data['total'] === 1);
        $this->get('/dashboard?analytics_period=today')->assertOk()->assertSee('No tickets match these graph filters.')
            ->assertViewHas('analytics', fn ($data) => $data['total'] === 0 && $data['departments']->isEmpty());
        $this->getJson('/dashboard?analytics_period=invalid')->assertUnprocessable()->assertJsonValidationErrors('analytics_period');
        $this->getJson('/dashboard?analytics_period=custom')->assertUnprocessable()->assertJsonValidationErrors(['analytics_from', 'analytics_to']);
        $this->getJson('/dashboard?analytics_period=custom&analytics_from=2026-11-09&analytics_to=2026-11-01')
            ->assertUnprocessable()->assertJsonValidationErrors('analytics_to');
        $this->getJson('/dashboard?analytics_department_id=9999')->assertUnprocessable()->assertJsonValidationErrors('analytics_department_id');
        $this->travelBack();
    }

    public function test_portal_uses_existing_categories_and_omits_unsupported_fields(): void
    {
        $this->get('/')->assertOk()->assertSee('What Do You Need Help With?')->assertDontSee('home-service-card')->assertDontSee('Track an Existing Ticket');
        $this->get('/tickets/create?step=issue&category=1')->assertOk()->assertSee('name="task_id"', false)
            ->assertDontSee('name="priority"', false)->assertDontSee('name="subject"', false)
            ->assertDontSee('Ticket Timeline');
    }

    public function test_submission_maps_to_legacy_columns_and_does_not_create_a_requester(): void
    {
        $this->post('/tickets', [
            'email' => 'requester@pmma.edu.ph', 'task_id' => 1,
            'support_specifics' => 'The office network connection is unavailable.',
            'submission_token' => (string) Str::uuid(), 'employee_id' => 999,
        ])->assertRedirect();
        $this->assertDatabaseCount('employees', 1);
        $this->assertDatabaseHas('supports', ['employee_id' => 1, 'task_id' => 1, 'status' => 'Pending',
            'support_specifics' => 'The office network connection is unavailable.', 'technician_id' => null]);
        Mail::assertSent(Mailer::class, 1);
    }

    public function test_repeated_submission_token_does_not_duplicate_a_ticket(): void
    {
        $payload = ['email' => 'requester@pmma.edu.ph', 'task_id' => 1,
            'support_specifics' => 'Network unavailable.', 'submission_token' => (string) Str::uuid()];
        $this->post('/tickets', $payload)->assertRedirect();
        $this->post('/tickets', $payload)->assertRedirect();
        $this->assertDatabaseCount('supports', 1);
        Mail::assertSent(Mailer::class, 1);
    }

    public function test_unknown_email_and_public_technician_assignment_are_rejected(): void
    {
        $payload = ['email' => 'unknown@example.test', 'task_id' => 1,
            'support_specifics' => 'Network unavailable.', 'submission_token' => (string) Str::uuid()];
        $this->post('/tickets', $payload)->assertSessionHasErrors('email');
        $payload['email'] = 'requester@pmma.edu.ph'; $payload['technician_id'] = 1;
        $this->post('/tickets', $payload)->assertSessionHasErrors('technician_id');
        $this->assertDatabaseCount('supports', 0);
    }

    public function test_public_tracking_preserves_numeric_references_without_exposing_requester_details(): void
    {
        $ticket = $this->ticket();
        $this->get('/track?reference='.$ticket->id)->assertOk()->assertSee('TICKET #'.$ticket->id)
            ->assertSee('Pending')->assertDontSee('requester@pmma.edu.ph')->assertDontSee('Test Requester')
            ->assertDontSee('Ticket Timeline')->assertDontSee('Priority');
    }

    public function test_staff_updates_existing_status_technician_and_findings(): void
    {
        $ticket = $this->ticket();
        $this->actingAs($this->staff())->post('/dashboard/tickets/'.$ticket->id, [
            'status' => 'Completed', 'technician_id' => 1, 'findings' => 'Replaced the network cable.',
        ])->assertRedirect('/dashboard/tickets/'.$ticket->id);
        $this->assertDatabaseHas('supports', ['id' => $ticket->id, 'status' => 'Completed',
            'technician_id' => 1, 'findings' => 'Replaced the network cable.']);
    }

    public function test_staff_cannot_save_statuses_from_the_incompatible_zip_schema(): void
    {
        $ticket = $this->ticket();
        $this->actingAs($this->staff())->post('/dashboard/tickets/'.$ticket->id, ['status' => 'In Progress'])
            ->assertSessionHasErrors('status');
        $this->assertSame('Pending', $ticket->fresh()->status);
    }

    public function test_supported_catalog_records_save_without_nonexistent_timestamp_columns(): void
    {
        $this->actingAs($this->staff());
        $this->post('/dashboard/settings/departments', ['name' => 'Library'])->assertRedirect();
        $this->post('/dashboard/settings/technicians', ['name' => 'Another Technician', 'email' => 'another@example.test'])->assertRedirect();
        $this->post('/dashboard/settings/services/1', ['name' => 'Network Support', 'content' => 'Network help', 'icon' => 'bi bi-wifi'])->assertRedirect();
        $this->assertDatabaseHas('departments', ['name' => 'Library']);
        $this->assertDatabaseHas('tasks', ['id' => 1, 'name' => 'Network Support']);
        $this->assertDatabaseHas('technicians', ['email' => 'another@example.test']);
    }

    public function test_dashboard_remains_protected_and_filters_existing_records(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->ticket();
        $this->actingAs($this->staff())->get('/dashboard?status=Completed')->assertOk()
            ->assertSee('No support tickets match your filters.')->assertDontSee('Activity Logs');
        $this->get('/dashboard?status=Pending')->assertOk()->assertSee('Test Requester');
    }

    public function test_login_through_admin_returns_to_dashboard_and_logout_ends_session(): void
    {
        $staff = $this->staff();
        $this->get('/admin')->assertRedirect('/login');
        $this->post('/login', ['email' => $staff->email, 'password' => 'test-password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($staff);
        $this->get('/admin')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk()->assertSee('STAFF WORKSPACE');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_login_does_not_open_dashboard(): void
    {
        $staff = $this->staff();
        $this->post('/login', ['email' => $staff->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_legacy_admin_and_error_links_resolve(): void
    {
        $this->actingAs($this->staff());
        foreach (['/admin.php', '/admin/index.php', '/test'] as $path) { $this->get($path)->assertRedirect('/dashboard'); }
        $this->get('/admin/login')->assertRedirect('/login');
        $this->get('/failed')->assertOk()->assertSee('Request could not be completed.');
        $this->get('/notify')->assertRedirect('/tickets/create');
    }

    public function test_staff_creation_associates_existing_employee_and_technician(): void
    {
        $this->actingAs($this->staff())->post('/dashboard/tickets', [
            'department_id' => 1, 'employee_id' => 1, 'task_id' => 1, 'support_specifics' => 'Please reconnect the network.',
            'technician_id' => 1, 'submission_token' => (string) Str::uuid(),
        ])->assertRedirect();
        $ticket = Support::firstOrFail();
        $this->assertSame(1, (int) $ticket->employee_id);
        $this->assertSame(1, (int) $ticket->technician_id);
        $this->get('/dashboard/tickets/'.$ticket->id)->assertOk()->assertSee('Please reconnect the network.');
    }

    public function test_technician_dashboard_requires_login_and_supports_existing_schema(): void
    {
        $ticket = $this->ticket();
        $this->get('/tech')->assertRedirect('/login');
        $this->post('/tech/tickets/'.$ticket->id, ['status' => 'Completed'])->assertRedirect('/login');
        $this->actingAs($this->staff())->get('/tech')->assertOk()->assertSee('Tickets to work on')
            ->assertSee('Diagnosis &amp; update', false)->assertSee('name="findings"', false)
            ->assertDontSee('name="priority"', false)->assertDontSee('dashboard-table');
        $this->post('/tech/tickets/'.$ticket->id, ['status' => 'Pending', 'findings' => 'Checking the network.', 'technician_id' => 1])
            ->assertSessionHasNoErrors()->assertRedirect(route('tech.index'));
        $this->assertDatabaseHas('supports', ['id' => $ticket->id, 'findings' => 'Checking the network.', 'technician_id' => 1]);
    }

    public function test_technician_dashboard_sorts_by_priority_and_filters_tickets(): void
    {
        (require base_path('database/migrations/2026_11_11_000002_add_ticket_priority.php'))->up();
        $low = $this->ticket(); $low->update(['priority' => 'Low', 'support_specifics' => 'Low priority concern']);
        $high = $this->ticket(); $high->update(['priority' => 'High', 'support_specifics' => 'High priority concern', 'technician_id' => 1]);
        $urgent = $this->ticket(); $urgent->update(['priority' => 'Urgent', 'support_specifics' => 'Urgent priority concern']);
        $done = $this->ticket(); $done->update(['status' => 'Completed', 'support_specifics' => 'Finished concern']);
        $this->actingAs($this->staff())->get('/tech')->assertOk()
            ->assertSeeInOrder(['Urgent priority concern', 'High priority concern', 'Low priority concern'])->assertDontSee('Finished concern');
        $this->get('/tech?priority=High&technician=1')->assertOk()->assertSee('High priority concern')->assertDontSee('Urgent priority concern');
        $this->get('/tech?technician=unassigned')->assertOk()->assertSee('Urgent priority concern')->assertDontSee('High priority concern');
        $this->get('/tech?status=Completed')->assertOk()->assertSee('Finished concern')->assertDontSee('Urgent priority concern');
        $this->get('/tech?q=Urgent')->assertOk()->assertSee('Urgent priority concern')->assertDontSee('High priority concern');
        $this->getJson('/tech?priority=Invalid')->assertUnprocessable();
        $this->getJson('/tech?technician=999999')->assertUnprocessable();
    }

    public function test_technician_can_save_diagnosis_status_assignment_and_priority(): void
    {
        (require base_path('database/migrations/2026_11_11_000002_add_ticket_priority.php'))->up();
        $ticket = $this->ticket();
        $this->actingAs($this->staff())->post('/tech/tickets/'.$ticket->id, [
            'status' => 'Completed', 'priority' => 'High', 'technician_id' => 1,
            'findings' => 'Faulty cable replaced. Connectivity restored.', 'view_status' => 'all',
        ])->assertSessionHasNoErrors()->assertRedirect(route('tech.index', ['status' => 'all']));
        $this->assertDatabaseHas('supports', ['id' => $ticket->id, 'priority' => 'High',
            'status' => 'Completed', 'technician_id' => 1, 'findings' => 'Faulty cable replaced. Connectivity restored.']);
        Mail::assertSent(\App\Mail\MailerStatus::class, 1);
        $this->post('/tech/tickets/'.$ticket->id, ['status' => 'In Progress', 'priority' => 'Invalid'])
            ->assertSessionHasErrors(['status', 'priority']);
        $this->assertDatabaseHas('supports', ['id' => $ticket->id, 'status' => 'Completed', 'priority' => 'High']);
    }

    public function test_staff_requester_must_be_active_and_belong_to_the_department(): void
    {
        $this->actingAs($this->staff());
        DB::table('departments')->insert(['id' => 2, 'name' => 'Other Department']);
        $payload = ['department_id' => 2, 'employee_id' => 1, 'task_id' => 1,
            'support_specifics' => 'Network unavailable.', 'submission_token' => (string) Str::uuid()];
        $this->post('/dashboard/tickets', $payload)->assertSessionHasErrors('employee_id');
        $payload['department_id'] = 1;
        DB::table('employees')->where('id', 1)->update(['deleted_at' => now()]);
        $this->post('/dashboard/tickets', $payload)->assertSessionHasErrors('employee_id');
        $this->get('/dashboard/tickets/create')->assertOk()->assertDontSee('Test Requester');
        $this->assertDatabaseCount('supports', 0);
        Mail::assertNothingSent();
    }

    public function test_public_wizard_uses_catalog_suggestions_and_keeps_manual_details(): void
    {
        DB::table('service_issues')->insert(['name' => 'Cannot connect to Wi-Fi', 'task_id' => 1]);
        DB::table('service_issues')->insert(['name' => 'Other category issue', 'task_id' => 999]);
        $this->get('/tickets/create')->assertOk()->assertSee('Internet/Network Connectivity')
            ->assertDontSee('name="email"', false);
        $this->get('/tickets/create?step=issue&category=1')->assertOk()
            ->assertSee('Cannot connect to Wi-Fi')->assertDontSee('Other category issue')
            ->assertSee('Other / Write my own issue');
        $this->post('/tickets/details', ['task_id' => 1, 'support_specifics' => 'My manually written issue'])
            ->assertRedirect(route('tickets.create', ['step' => 'email']));
        $this->get('/tickets/create?step=email')->assertOk()->assertSee('My manually written issue')->assertSee('PMMA Email Address');
        $this->post('/tickets/details', ['task_id' => 1, 'support_specifics' => ''])->assertSessionHasErrors('support_specifics');
    }

    public function test_service_selection_allows_manual_details_before_issue_catalog_migration(): void
    {
        Schema::drop('service_issues');
        $this->get('/tickets/create')->assertOk()->assertSee('service-choice-grid')
            ->assertSee('aria-labelledby="service-title-1"', false)
            ->assertSee(route('tickets.create', ['step' => 'issue', 'category' => 1]));
        $this->get('/tickets/create?step=issue&category=1')->assertOk()
            ->assertSee('Describe your issue')->assertSee('No common issues recorded for this service yet.')
            ->assertSee('name="support_specifics"', false);
        $this->post('/tickets/details', ['task_id' => 1, 'support_specifics' => 'My manual issue'])
            ->assertRedirect(route('tickets.create', ['step' => 'email']));
        $this->get('/tickets/create?step=email')->assertOk()->assertSee('My manual issue');
        $this->get('/services')->assertOk()->assertSee('service-choice-grid');
    }

    public function test_new_pmma_email_creates_an_inactive_employee_and_submits_ticket(): void
    {
        $this->postJson('/tickets/requester', ['email' => ' NEW.USER@PMMA.EDU.PH '])->assertOk()
            ->assertExactJson(['valid' => true, 'registration_required' => true]);
        $this->assertDatabaseCount('employees', 1);
        $payload = ['email' => ' NEW.USER@PMMA.EDU.PH ', 'name' => 'New User', 'registration_department_id' => 1,
            'task_id' => 1, 'support_specifics' => 'Network unavailable.', 'submission_token' => (string) Str::uuid()];
        $this->post('/tickets', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $employee = \App\Models\Employee::withTrashed()->where('email', 'new.user@pmma.edu.ph')->firstOrFail();
        $this->assertTrue($employee->trashed());
        $this->assertEquals(1, $employee->activation_pending);
        $this->assertDatabaseHas('supports', ['employee_id' => $employee->id, 'support_specifics' => 'Network unavailable.']);
        $this->get('/tickets/receipt?ticket='.Support::firstOrFail()->id)->assertOk()->assertSee('awaiting administrator activation');
        $this->post('/tickets', $payload)->assertRedirect();
        $this->assertDatabaseCount('employees', 2);
        $this->assertDatabaseCount('supports', 1);
        $this->actingAs($this->staff())->get('/dashboard/settings?tab=employees&status=activation')->assertOk()
            ->assertSee('NEW USER')->assertDontSee('Test Requester');
        $this->getJson('/dashboard/tables/catalog/employees?status=activation')->assertOk()
            ->assertJsonPath('recordsTotal', 1);
        $this->post('/dashboard/settings/employees/'.$employee->id, [
            'name' => 'New User', 'email' => $employee->email, 'department_id' => 1, 'active' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertFalse($employee->fresh()->trashed());
        $this->assertEquals(0, $employee->fresh()->activation_pending);
    }

    public function test_common_issues_group_historical_concerns_by_frequency_without_exposing_ticket_text(): void
    {
        $descriptions = [
            'RESET OUTLOOK PASSWORD for private.person@pmma.edu.ph',
            'Please reset my password, requester Private Person',
            'Account password reset requested',
            'Wi-Fi cannot connect in private room 123',
            'Wireless connection unavailable',
            "Doesn't have valid IP configuration.",
            'Creation of Email Account',
            'Set-up of projector and projector screen at the hall',
            'blue screen', 'Phone cannot connect.', 'Assist in Cable Management', 'Replace printer toner', 'Install software',
            null, '', 'NULL', 'test',
        ];
        foreach ($descriptions as $description) {
            $ticket = $this->ticket(); $ticket->update(['support_specifics' => $description]);
        }
        $otherService = $this->ticket(); $otherService->update(['task_id' => 999, 'support_specifics' => 'RESET PASSWORD']);
        $groups = app(\App\Services\EncounteredIssues::class)->forService(1);
        $this->assertSame('Password reset / account access', $groups[0]->name);
        $this->assertSame(3, $groups[0]->reports);
        $this->assertSame('Wi-Fi connection', $groups[1]->name);
        $this->assertSame(2, $groups[1]->reports);
        $this->assertCount(9, $groups);
        $this->get('/tickets/create?step=issue&category=1')->assertOk()
            ->assertSeeInOrder(['Password reset / account access', 'Wi-Fi connection', 'Other / Write my own issue', 'Your concern'])
            ->assertSee('3 previous tickets')->assertSee('data-issue-editor', false)
            ->assertDontSee('private.person@pmma.edu.ph')->assertDontSee('Private Person')->assertDontSee('private room 123');
        $this->assertCount(2, app(\App\Services\EncounteredIssues::class)->forService(1, 2));
    }

    public function test_public_email_domain_registration_details_and_inactive_accounts_are_validated(): void
    {
        $this->postJson('/tickets/requester', ['email' => 'person@pmma.edu.ph.evil.test'])->assertUnprocessable();
        $payload = ['email' => 'new@pmma.edu.ph', 'task_id' => 1,
            'support_specifics' => 'Network unavailable.', 'submission_token' => (string) Str::uuid()];
        $this->post('/tickets', $payload)->assertSessionHasErrors(['name', 'registration_department_id']);
        $payload['email'] = 'requester@pmma.edu.ph';
        DB::table('employees')->where('id', 1)->update(['deleted_at' => now()]);
        $this->post('/tickets', $payload)->assertSessionHasErrors('email');
        $this->assertDatabaseCount('supports', 0);
        $this->assertDatabaseCount('employees', 1);
    }

    public function test_unregistered_cadet_can_register_with_uppercase_full_name_while_submitting_ticket(): void
    {
        $this->postJson('/tickets/requester', ['email' => 'cadet@pmma.edu.ph'])->assertOk()
            ->assertExactJson(['valid' => true, 'registration_required' => true]);
        $payload = ['email' => 'cadet@pmma.edu.ph', 'name' => '  Juan Dela Cruz  ',
            'registration_department_id' => 44, 'task_id' => 1,
            'support_specifics' => 'Cannot connect in the dormitory.', 'submission_token' => (string) Str::uuid()];
        $this->post('/tickets', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $cadet = \App\Models\Employee::withTrashed()->where('email', 'cadet@pmma.edu.ph')->firstOrFail();
        $this->assertSame('JUAN DELA CRUZ', $cadet->name);
        $this->assertEquals(44, $cadet->department_id);
        $this->assertTrue($cadet->trashed());
        $this->assertEquals(1, $cadet->activation_pending);
        $this->assertDatabaseHas('supports', ['employee_id' => $cadet->id]);
        $this->post('/tickets', $payload)->assertRedirect();
        $this->assertDatabaseCount('employees', 2);
        $this->assertDatabaseCount('supports', 1);
    }

    public function test_admin_can_register_suggested_issues_per_service(): void
    {
        $this->get('/dashboard/settings/issues/create')->assertRedirect('/login');
        $this->actingAs($this->staff())->get('/dashboard/settings/issues/create')->assertOk()->assertSee('Service Category');
        $this->post('/dashboard/settings/issues', ['name' => 'No network connection', 'task_id' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/dashboard/settings?tab=issues')->assertOk()->assertSee('No network connection');
        $this->getJson('/dashboard/tables/catalog/issues')->assertOk()->assertJsonPath('recordsTotal', 1);
        $this->get('/tickets/create?step=issue&category=1')->assertOk()->assertSee('No network connection');
    }

    public function test_receipt_requires_own_session_and_tracking_handles_missing_ticket(): void
    {
        $ticket = $this->ticket();
        $this->get('/tickets/receipt?ticket='.$ticket->id)->assertNotFound();
        $this->withSession(['helpdesk' => ['receipts' => [$ticket->id => true]]])
            ->get('/tickets/receipt?ticket='.$ticket->id)->assertOk()->assertSee('Your ticket has been submitted.');
        $this->get('/track?reference=999999')->assertOk()->assertSee('We could not find a matching ticket.');
    }

    public function test_requester_lookup_uses_active_directory_records(): void
    {
        $this->postJson('/tickets/requester', ['email' => 'requester@pmma.edu.ph'])->assertOk()
            ->assertExactJson(['valid' => true]);
        DB::table('employees')->where('id', 1)->update(['deleted_at' => now()]);
        $this->postJson('/tickets/requester', ['email' => 'requester@pmma.edu.ph'])->assertUnprocessable();
    }

    public function test_request_forms_validate_email_without_exposing_directory_details(): void
    {
        DB::table('departments')->where('id', 1)->update(['name' => 'Private Directory Department']);
        $this->get('/tickets/create')->assertOk()
            ->assertSee('What do you need help with?')
            ->assertDontSee('Test Requester')->assertDontSee('Private Directory Department')
            ->assertDontSee('requester-name')->assertDontSee('requester-department');
        $this->actingAs($this->staff())->get('/dashboard/tickets/create?email=requester@pmma.edu.ph')->assertOk()
            ->assertSee('Test Requester')->assertSee('Private Directory Department')
            ->assertSeeInOrder(['Department</label>', 'Employee Name</label>', 'Service Category</label>'], false)
            ->assertDontSee('name="email"', false)->assertDontSee('data-requester-url', false);
        $this->postJson('/tickets/requester', ['email' => 'requester@pmma.edu.ph'])->assertOk()->assertExactJson(['valid' => true]);
    }

    public function test_employee_create_update_deactivation_and_restoration(): void
    {
        $this->actingAs($this->staff());
        $payload = ['name' => 'Directory User', 'email' => 'directory@example.test', 'department_id' => 1, 'active' => 1];
        $this->post('/dashboard/settings/employees', $payload)->assertRedirect();
        $id = DB::table('employees')->where('email', $payload['email'])->value('id');
        $this->assertNotNull($id);
        $payload['name'] = 'Updated User'; $payload['active'] = 0;
        $this->post('/dashboard/settings/employees/'.$id, $payload)->assertRedirect();
        $this->assertNotNull(DB::table('employees')->where('id', $id)->value('deleted_at'));
        $payload['active'] = 1;
        $this->post('/dashboard/settings/employees/'.$id, $payload)->assertRedirect();
        $this->assertDatabaseHas('employees', ['id' => $id, 'name' => 'Updated User', 'deleted_at' => null]);
    }

    public function test_every_supported_catalog_type_can_be_created_and_updated(): void
    {
        $this->actingAs($this->staff());
        $catalogs = [
            'departments' => ['name' => 'New Office'],
            'technicians' => ['name' => 'New Technician', 'email' => 'new.tech@example.test'],
            'services' => ['name' => 'New Service', 'content' => 'New service description', 'icon' => 'bi bi-headset'],
        ];
        foreach ($catalogs as $type => $payload) {
            $table = $type === 'services' ? 'tasks' : $type;
            $this->post('/dashboard/settings/'.$type, $payload)->assertRedirect();
            $id = DB::table($table)->where('name', $payload['name'])->value('id');
            $payload['name'] .= ' Updated';
            $this->post('/dashboard/settings/'.$type.'/'.$id, $payload)->assertRedirect();
            $this->assertDatabaseHas($table, ['id' => $id, 'name' => $payload['name']]);
            $this->get('/dashboard/settings/'.$type.'/'.$id)->assertOk();
        }
    }

    public function test_invalid_directory_edits_do_not_change_saved_records(): void
    {
        $this->actingAs($this->staff())->post('/dashboard/settings/employees/1', [
            'name' => 'Changed', 'email' => 'not-an-email', 'department_id' => 999999, 'active' => 1,
        ])->assertSessionHasErrors(['email', 'department_id']);
        $this->assertDatabaseHas('employees', ['id' => 1, 'name' => 'Test Requester']);
    }

    public function test_cadet_registration_normalizes_input_and_rejects_nonstudent_department(): void
    {
        $this->post('/student/store', ['name' => ' Cadet Name ', 'email' => 'CADET@EXAMPLE.TEST', 'department_id' => 44])->assertRedirect('/student');
        $this->assertDatabaseHas('employees', ['name' => 'CADET NAME', 'email' => 'cadet@example.test', 'department_id' => 44]);
        $this->post('/student/store', ['name' => 'Other', 'email' => 'other@example.test', 'department_id' => 1])->assertSessionHasErrors('department_id');
    }

    public function test_signed_survey_saves_once_and_ignores_a_forged_posted_ticket_id(): void
    {
        $ticket = $this->ticket();
        $formUrl = URL::temporarySignedRoute('take-survey', now()->addHour(), ['taskId' => $ticket->id]);
        $this->get($formUrl)->assertOk()->assertSee('method="post"', false);
        $url = URL::temporarySignedRoute('survey', now()->addHour(), ['support_id' => $ticket->id]);
        $payload = ['satisfactionRating' => 1, 'feedback' => 'Helpful support.', 'support_id' => 999999];
        $this->post($url, $payload)->assertRedirect('/');
        $this->post($url, $payload)->assertRedirect('/');
        $this->assertDatabaseCount('ratings_result', 1);
        $this->assertDatabaseHas('ratings_result', ['support_id' => $ticket->id, 'rating_id' => 1]);
        $this->actingAs($this->staff())->get('/dashboard/surveys')->assertOk()->assertSee('Helpful support.');
    }

    public function test_unsigned_and_invalid_survey_submissions_are_rejected(): void
    {
        $ticket = $this->ticket();
        $this->post('/survey', ['support_id' => $ticket->id, 'satisfactionRating' => 1])->assertForbidden();
        $url = URL::temporarySignedRoute('survey', now()->addHour(), ['support_id' => $ticket->id]);
        $this->post($url, ['satisfactionRating' => 99999])->assertSessionHasErrors('satisfactionRating');
        $this->get('/survey?support_id='.$ticket->id.'&satisfactionRating=1')->assertRedirect('/');
        $this->assertDatabaseCount('ratings_result', 0);
    }

    public function test_profile_and_password_changes_persist_and_old_password_is_rejected(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->patch('/profile', ['name' => 'Updated Staff', 'email' => $staff->email])->assertRedirect('/profile');
        $this->assertSame('Updated Staff', $staff->fresh()->name);
        $this->put('/password', ['current_password' => 'wrong', 'password' => 'new-test-password', 'password_confirmation' => 'new-test-password'])
            ->assertSessionHasErrors('current_password', null, 'updatePassword');
        $this->put('/password', ['current_password' => 'test-password', 'password' => 'new-test-password', 'password_confirmation' => 'new-test-password'])->assertRedirect();
        $this->assertTrue(Hash::check('new-test-password', $staff->fresh()->password));
    }

    public function test_password_recovery_writes_and_consumes_reset_token(): void
    {
        Notification::fake();
        $staff = $this->staff();
        $this->post('/forgot-password', ['email' => $staff->email])->assertSessionHasNoErrors();
        $token = null;
        Notification::assertSentTo($staff, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token; return true;
        });
        $this->assertDatabaseHas('password_resets', ['email' => $staff->email]);
        $this->post('/reset-password', ['email' => $staff->email, 'token' => $token,
            'password' => 'recovered-password', 'password_confirmation' => 'recovered-password'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('recovered-password', $staff->fresh()->password));
        $this->assertDatabaseMissing('password_resets', ['email' => $staff->email]);
    }

    public function test_reports_and_filters_include_existing_records_for_inactive_requesters(): void
    {
        $ticket = $this->ticket(); $ticket->update(['status' => 'Completed']);
        DB::table('employees')->where('id', 1)->update(['deleted_at' => now()]);
        $this->actingAs($this->staff())->get('/dashboard?department_id=1&status=Completed')->assertOk()->assertSee('Test Requester');
        $this->get('/report')->assertOk()->assertSee($ticket->support_specifics);
        $this->get('/report?from=2026-12-01&to=2026-01-01')->assertSessionHasErrors('to');
    }

    public function test_existing_email_templates_render_without_code_errors(): void
    {
        Mail::swap(new \Illuminate\Mail\MailManager($this->app));
        $ticket = $this->ticket();
        $this->assertStringContainsString((string) $ticket->id, (new Mailer($ticket->id))->render());
        $mail = new \App\Mail\MailerStatus($ticket->id, 'Completed', $ticket->created_at,
            'Test Requester', 'ITSU', 'Internet/Network Connectivity', $ticket->support_specifics,
            'Cable replaced.', 'Test Technician', now());
        $this->assertStringContainsString('Completed', $mail->render());
    }

    public function test_all_active_public_and_staff_page_layouts_render(): void
    {
        $ticket = $this->ticket();
        foreach (['/', '/services', '/services/1', '/services?q=network', '/tickets/create', '/track', '/queue',
            '/faq', '/contact', '/privacy', '/phone', '/student', '/login', '/forgot-password', '/register', '/failed'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->actingAs($this->staff());
        foreach (['/dashboard', '/admin/index', '/support', '/tech', '/add', '/profile', '/report', '/dashboard/queue', '/dashboard/surveys',
            '/dashboard/tickets/'.$ticket->id, '/dashboard/tickets/create', '/dashboard/settings',
            '/dashboard/settings?tab=services', '/dashboard/settings?tab=departments', '/dashboard/settings?tab=technicians',
            '/dashboard/settings/employees/create', '/dashboard/settings/departments/create',
            '/dashboard/settings/technicians/create', '/dashboard/settings/services/create'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_accomplishment_report_uses_completion_date_and_manual_signatories(): void
    {
        $ticket = $this->ticket();
        $ticket->update(['status' => 'Completed', 'findings' => 'Restored the network connection.',
            'technician_id' => 1, 'created_at' => '2026-09-01 08:00:00', 'updated_at' => '2026-10-20 10:00:00']);
        $this->actingAs($this->staff())->get('/report?from=2026-10-16&to=2026-10-31&technician_id=1&prepared_by=Manual+Preparer')
            ->assertOk()->assertSee('Restored the network connection.')->assertSee('October 20, 2026')
            ->assertSee('Manual Preparer')->assertSee('Print / Preview PDF')->assertDontSee('window.print()', false);
        $this->get('/report?from=2026-09-01&to=2026-09-30')->assertOk()->assertDontSee('Restored the network connection.');
    }

    public function test_report_pdf_is_protected_and_uses_the_original_page_size(): void
    {
        $this->get('/report/pdf')->assertRedirect('/login');
        $ticket = $this->ticket(); $ticket->update(['status' => 'Completed', 'findings' => 'Repaired network.']);
        $this->actingAs($this->staff());
        $before = Support::find($ticket->id)->toArray();
        $response = $this->get('/report/pdf?prepared_by=Manual+Preparer&noted_by=Manual+Approver');
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('inline;', $response->headers->get('Content-Disposition'));
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pages = $pdf->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($response->getContent()));
        $size = $pdf->getTemplateSize($pdf->importPage(1));
        $this->assertSame(1, $pages);
        // FPDI's default units are millimetres; compare the original PDF's point dimensions.
        $this->assertEqualsWithDelta(595.44, $size['width'] * 72 / 25.4, 0.001);
        $this->assertEqualsWithDelta(841.68, $size['height'] * 72 / 25.4, 0.001);
        $this->assertSame($before, Support::find($ticket->id)->toArray());
        $this->assertDatabaseCount('supports', 1);
    }

    public function test_report_download_is_a_pdf_attachment_and_invalid_filters_do_not_export(): void
    {
        $this->actingAs($this->staff())->get('/report/pdf?from=2026-10-16&to=2026-10-31&download=1')
            ->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="accomplishment-report-2026-10-16-to-2026-10-31.pdf"');
        $this->get('/report/pdf?from=2026-11-01&to=2026-10-01')->assertSessionHasErrors('to');
        $this->get('/report/pdf?technician_id=99999')->assertSessionHasErrors('technician_id');
        $this->get('/report/pdf?prepared_by='.str_repeat('A', 101))->assertSessionHasErrors('prepared_by');
    }

    public function test_long_accomplishments_paginate_without_changing_paper_dimensions(): void
    {
        $service = new \App\Services\Reports\AccomplishmentPdf();
        $bytes = $service->render([['date' => 'October 20, 2026', 'items' => [str_repeat('A long technical finding with details. ', 110)]]],
            '2026-10-16', '2026-10-31', ['prepared_by' => 'Preparer', 'noted_by' => 'Approver']);
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'pt');
        $pages = $pdf->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($bytes));
        $this->assertGreaterThan(1, $pages);
        for ($page = 1; $page <= $pages; $page++) {
            $size = $pdf->getTemplateSize($pdf->importPage($page));
            $this->assertEqualsWithDelta(595.44, $size['width'], 0.001);
            $this->assertEqualsWithDelta(841.68, $size['height'], 0.001);
        }
    }

    public function test_official_report_template_is_the_unchanged_supplied_file(): void
    {
        $this->assertSame('f48a60f70b022f43a2b720a3864e4ae5dbaf18e882ebf3026733f4cc106e4eb6',
            hash_file('sha256', resource_path('reports/ar_blank.pdf')));
    }

    public function test_dashboard_table_endpoints_require_authentication(): void
    {
        foreach (['tickets', 'catalog/employees', 'catalog/departments', 'catalog/technicians', 'catalog/services', 'surveys'] as $table) {
            $this->getJson('/dashboard/tables/'.$table)->assertUnauthorized();
        }
    }

    public function test_public_dashboard_shows_latest_ten_tickets_even_when_older_than_a_day(): void
    {
        $ids = [];
        for ($index = 0; $index < 12; $index++) {
            $ticket = $this->ticket();
            $ticket->update(['created_at' => now()->subDays(20 - $index)]);
            $ids[] = $ticket->id;
        }
        $response = $this->get('/')->assertOk()->assertSee('The 10 most recently submitted support requests.')
            ->assertDontSee('requester@pmma.edu.ph')->assertDontSee('Test Requester');
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $rows = $xpath->query('//*[@id="recent-tickets"]//tbody/tr');
        $this->assertCount(10, $rows);
        $this->assertSame('#'.$ids[11], trim($rows->item(0)->firstChild->textContent));
        $this->assertSame('#'.$ids[2], trim($rows->item(9)->firstChild->textContent));
    }

    public function test_ticket_datatable_searches_all_records_and_paginates_sorted_results(): void
    {
        $rows = [];
        for ($index = 0; $index < 27; $index++) {
            $rows[] = ['employee_id' => 1, 'task_id' => 1, 'technician_id' => 1, 'status' => 'Pending',
                'support_specifics' => $index === 26 ? 'Unique request beyond the first page' : 'Standard request '.$index,
                'ip' => '127.0.0.1', 'created_at' => now(), 'updated_at' => now()];
        }
        DB::table('supports')->insert($rows);
        $ids = Support::orderBy('id')->pluck('id')->all();
        $this->actingAs($this->staff());
        $query = http_build_query(['draw' => 7, 'start' => 15, 'length' => 10, 'order' => [['column' => 0, 'dir' => 'asc']]]);
        $response = $this->getJson('/dashboard/tables/tickets?'.$query)->assertOk()
            ->assertJsonPath('draw', 7)->assertJsonPath('recordsTotal', 27)->assertJsonPath('recordsFiltered', 27);
        $html = $response->json('rows');
        $this->assertSame(10, substr_count($html, '<tr>'));
        $this->assertStringContainsString('>#'.$ids[15].'</a>', $html);
        $query = http_build_query(['search' => ['value' => 'Unique request beyond']]);
        $this->getJson('/dashboard/tables/tickets?'.$query)->assertOk()->assertJsonPath('recordsFiltered', 1);
        $query = http_build_query(['search' => ['value' => 'ITSU']]);
        $this->getJson('/dashboard/tables/tickets?'.$query)->assertOk()->assertJsonPath('recordsFiltered', 27);
        $this->getJson('/dashboard/tables/tickets?status=Completed')->assertOk()->assertJsonPath('recordsTotal', 0);
    }

    public function test_catalog_datatables_sort_search_and_escape_names_for_all_tabs(): void
    {
        DB::table('employees')->insert(['name' => '<script>alert(1)</script>', 'email' => 'late@example.test',
            'department_id' => 1, 'deleted_at' => now()]);
        $this->actingAs($this->staff());
        foreach (['employees' => 'late@example.test', 'departments' => 'ITSU', 'technicians' => 'Test Technician',
            'services' => 'Network assistance'] as $type => $term) {
            $query = http_build_query(['draw' => 2, 'search' => ['value' => $term], 'order' => [['column' => 0, 'dir' => 'desc']]]);
            $response = $this->getJson('/dashboard/tables/catalog/'.$type.'?'.$query)->assertOk()
                ->assertJsonPath('draw', 2)->assertJsonPath('recordsFiltered', 1);
            $html = $response->json('rows');
            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringNotContainsString('colspan=', $html);
            if ($type === 'employees') {
                $this->assertStringContainsString('&lt;script&gt;', $html);
                $this->assertStringContainsString('Inactive', $html);
            }
        }
        $query = http_build_query(['search' => ['value' => 'no-such-catalog-record']]);
        $this->getJson('/dashboard/tables/catalog/departments?'.$query)->assertOk()->assertJsonPath('recordsFiltered', 0);
    }

    public function test_ticket_and_survey_tables_sort_related_fields_and_preserve_inactive_requesters(): void
    {
        $first = $this->ticket();
        DB::table('employees')->insert(['id' => 2, 'name' => 'Zeta Requester', 'email' => 'zeta@example.test',
            'department_id' => 1, 'deleted_at' => now()]);
        $second = Support::create(['employee_id' => 2, 'task_id' => 1, 'status' => 'Pending',
            'support_specifics' => 'Another request', 'ip' => '127.0.0.1']);
        DB::table('ratings')->insert(['id' => 2, 'name' => 'Needs improvement']);
        DB::table('ratings_result')->insert([
            ['support_id' => $first->id, 'rating_measure_id' => 0, 'rating_id' => 1, 'feedback' => 'Excellent support', 'created_at' => now()],
            ['support_id' => $second->id, 'rating_measure_id' => 1, 'rating_id' => 2, 'feedback' => 'Please improve response', 'created_at' => now()],
        ]);
        $this->actingAs($this->staff());
        $query = http_build_query(['order' => [['column' => 1, 'dir' => 'desc']]]);
        $response = $this->getJson('/dashboard/tables/tickets?'.$query)->assertOk();
        $this->assertLessThan(strpos($response->json('rows'), 'Test Requester'), strpos($response->json('rows'), 'Zeta Requester'));
        $query = http_build_query(['search' => ['value' => 'Overall satisfaction']]);
        $this->getJson('/dashboard/tables/surveys?'.$query)->assertOk()->assertJsonPath('recordsFiltered', 1);
        $query = http_build_query(['search' => ['value' => 'Please improve']]);
        $response = $this->getJson('/dashboard/tables/surveys?'.$query)->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertStringContainsString('Needs improvement', $response->json('rows'));
    }

    public function test_datatable_inputs_are_bounded_and_never_used_as_sql_identifiers(): void
    {
        $this->ticket(); $this->actingAs($this->staff());
        $this->getJson('/dashboard/tables/tickets?length=10000')->assertUnprocessable();
        $this->getJson('/dashboard/tables/tickets?order[0][dir]=DROP')->assertUnprocessable();
        $this->getJson('/dashboard/tables/tickets?start=-1')->assertUnprocessable();
        $query = http_build_query(['order' => [['column' => 20, 'dir' => 'asc']],
            'columns' => [['name' => 'supports; DROP TABLE supports']]]);
        $this->getJson('/dashboard/tables/tickets?'.$query)->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertDatabaseCount('supports', 1);
    }
    public function test_report_groups_duplicate_findings_once_per_day_and_accepts_safe_html(): void
    {
        $first = $this->ticket();
        $first->update(['status' => 'Completed', 'findings' => 'Connected laptop to printer.']);
        $second = $this->ticket();
        $second->update(['status' => 'Completed', 'findings' => 'CONNECTED  laptop to printer.']);
        $before = Support::orderBy('id')->get()->toArray();
        $response = $this->actingAs($this->staff())->post('/report', [
            'content_html' => '<p style="font-size:50px;text-align:center" onclick="evil()"><b>Custom title</b></p><script>evil()</script>',
        ])->assertOk()->assertViewHas('groups', fn ($groups) => count($groups) === 1 && count($groups[0]['items']) === 1)
          ->assertViewHas('contentHtml', '<p align="center"><b>Custom title</b></p>');
        $this->post('/report/pdf', ['content_html' => '<p align="center"><b>Custom title</b></p><p>Custom introduction.</p>'])
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame($before, Support::orderBy('id')->get()->toArray());
    }

    public function test_report_html_removes_external_resources_and_preserves_fixed_font_formatting(): void
    {
        $html = \App\Services\Reports\ReportContent::clean('<h1 style="font-family:Arial;color:red;text-align:center">Title</h1><p><i>Text</i><img src="http://example.com"><iframe>bad</iframe></p>');
        $this->assertSame('<p align="center"><b>Title</b></p><p align="left"><i>Text</i></p>', $html);
        $this->assertSame($html, \App\Services\Reports\ReportContent::clean($html));
        $this->assertSame(769.68, \App\Services\Reports\AccomplishmentPdf::TABLE_BOTTOM);
    }

}
