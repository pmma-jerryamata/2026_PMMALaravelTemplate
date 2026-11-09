<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function staff(bool $dev = false): User
    {
        $user = User::factory()->create(['password' => 'test-secret-123']);
        $user->is_staff = true;
        $user->is_dev_admin = $dev;
        $user->save();
        return $user;
    }

    public function test_public_ticket_submission_is_logged_without_request_body_or_secrets(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.5'])->post('/tickets', [
            'name' => 'Test Requester', 'email' => 'requester@example.com', 'department_id' => Department::first()->id,
            'category' => 'software', 'subject' => 'Private subject', 'description' => 'Sensitive description should never appear in audit details.',
            'priority' => 'normal', 'consent' => 1, 'password' => 'never-log-this', '_token' => 'private-token',
        ])->assertRedirect();
        $log = ActivityLog::where('operation', 'ticket.create')->sole();
        $this->assertSame('192.0.2.5', $log->ip_address);
        $this->assertSame('Test Requester (Requestee)', $log->actor_name);
        $this->assertSame('success', $log->outcome);
        $this->assertNotEmpty($log->details['reference']);
        $serialized = $log->toJson();
        foreach (['never-log-this', 'private-token', 'Sensitive description', 'Private subject'] as $secret) $this->assertStringNotContainsString($secret, $serialized);
    }

    public function test_login_and_logout_are_logged_with_the_correct_actor(): void
    {
        $user = $this->staff();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-secret'])->assertSessionHasErrors('email');
        $this->assertDatabaseHas('activity_logs', ['operation' => 'auth.login', 'outcome' => 'failed']);
        $this->post('/login', ['email' => $user->email, 'password' => 'test-secret-123'])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('activity_logs', ['operation' => 'auth.login', 'outcome' => 'success', 'user_id' => $user->id]);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertDatabaseHas('activity_logs', ['operation' => 'auth.logout', 'user_id' => $user->id, 'actor_name' => $user->name]);
        $this->assertStringNotContainsString('wrong-secret', ActivityLog::all()->toJson());
        $this->assertStringNotContainsString('test-secret-123', ActivityLog::all()->toJson());
    }

    public function test_only_dev_admin_can_view_logs_and_regular_staff_actions_are_still_logged(): void
    {
        $this->get('/dashboard/activity-logs')->assertRedirect('/login');
        $this->assertDatabaseHas('activity_logs', ['operation' => 'dashboard.activity-logs.view', 'outcome' => 'failed']);
        $staff = $this->staff();
        $this->actingAs($staff)->get('/dashboard')->assertOk()->assertDontSee('Activity Logs');
        $this->get('/dashboard/activity-logs')->assertForbidden();
        $this->getJson('/dashboard/activity-logs/data')->assertForbidden();
        $this->assertDatabaseHas('activity_logs', ['user_id' => $staff->id, 'operation' => 'dashboard.view']);
        $this->actingAs($this->staff(true))->get('/dashboard/activity-logs')->assertOk()->assertSee('System Activity Logs');
    }

    public function test_logs_support_combined_filters_sorting_and_pagination(): void
    {
        $staff = $this->staff();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.44'])->actingAs($staff)->postJson('/dashboard/settings/departments', ['name' => 'Audit Office', 'code' => 'AUDIT', 'is_active' => 1])->assertOk();
        $this->actingAs($this->staff(true));
        $query = http_build_query(['ip' => '192.0.2.44', 'user' => $staff->email, 'operation' => 'settings.departments.create', 'outcome' => 'success', 'length' => 10, 'draw' => 7, 'order' => [['column' => 0, 'dir' => 'desc']]]);
        $this->getJson('/dashboard/activity-logs/data?'.$query)->assertOk()->assertJsonPath('draw', 7)->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.operation', 'settings.departments.create');
        $this->getJson('/dashboard/activity-logs/data?length=100000')->assertUnprocessable();
        $this->getJson('/dashboard/activity-logs/data?to='.now('Asia/Manila')->format('Y-m-d'))->assertOk();
    }

    public function test_regular_staff_cannot_change_a_linked_dev_admin_account(): void
    {
        $dev = $this->staff(true);
        $technician = Technician::create(['name' => $dev->name, 'email' => $dev->email, 'user_id' => $dev->id]);
        $this->actingAs($this->staff())->postJson('/dashboard/settings/technicians/'.$technician->id, ['name' => 'Changed', 'email' => $dev->email, 'is_active' => 1, 'user_access' => 1, 'username' => 'changed', 'password' => 'attacker-secret-123', 'password_confirmation' => 'attacker-secret-123'])->assertForbidden();
        $this->assertNotSame('Changed', $dev->fresh()->name);
    }
}
