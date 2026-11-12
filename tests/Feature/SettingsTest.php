<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\Technician;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function staff(): User
    {
        $user = User::factory()->create(['password' => 'current-password-123']);
        $user->is_staff = true;
        $user->save();
        return $user;
    }

    public function test_settings_require_staff_access(): void
    {
        $this->get('/dashboard/settings')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->postJson('/dashboard/settings/departments', ['name' => 'Blocked'])->assertForbidden();
        $this->assertDatabaseMissing('departments', ['name' => 'Blocked']);
    }

    public function test_departments_are_editable_and_inactive_offices_are_hidden_from_requests(): void
    {
        $this->actingAs($this->staff())->get('/dashboard/settings')->assertOk()->assertSee('Service Categories');
        $this->postJson('/dashboard/settings/departments', ['name' => 'Test Office', 'code' => 'TEST', 'is_active' => 1])->assertOk();
        $department = Department::where('code', 'TEST')->firstOrFail();
        $this->get('/tickets/create')->assertSee('Test Office');
        $this->postJson('/dashboard/settings/departments', ['name' => 'Duplicate', 'code' => 'TEST', 'is_active' => 1])->assertUnprocessable();
        $this->postJson('/dashboard/settings/departments/'.$department->id, ['name' => 'Renamed Office', 'code' => 'TEST', 'is_active' => 0])->assertOk();
        $this->get('/tickets/create')->assertDontSee('Renamed Office');
    }

    public function test_service_settings_drive_public_directory_and_validation(): void
    {
        $this->actingAs($this->staff())->postJson('/dashboard/settings/services', ['slug' => 'test-service', 'title' => 'Test Service', 'icon' => 'wifi', 'description' => 'Test service description.', 'items' => "First concern\nSecond concern", 'is_active' => 1])->assertOk();
        $service = ServiceCategory::where('slug', 'test-service')->firstOrFail();
        $this->assertSame(['First concern', 'Second concern'], $service->items);
        $this->get('/')->assertSee('Test Service');
        $this->postJson('/dashboard/settings/services/'.$service->id, ['slug' => 'test-service', 'title' => 'Test Service', 'icon' => 'wifi', 'description' => 'Test service description.', 'items' => 'First concern', 'is_active' => 0])->assertOk();
        $this->get('/')->assertDontSee('Test Service');
        $this->get('/services/test-service')->assertNotFound();
    }

    public function test_technician_and_ticket_can_be_created_and_managed_inside_dashboard(): void
    {
        $this->actingAs($this->staff())->postJson('/dashboard/settings/technicians', ['name' => 'Test Tech', 'email' => 'tech@example.com', 'specialization' => 'Networking', 'is_active' => 1])->assertOk();
        $technician = Technician::where('email', 'tech@example.com')->firstOrFail();
        $this->postJson('/dashboard/tickets', ['submission_token' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Requester', 'email' => 'requester@example.com', 'department_id' => Department::first()->id, 'category' => 'internet-network', 'subject' => 'Network issue', 'description' => 'Internet connection is unavailable in the office.', 'priority' => 'high'])->assertOk();
        $ticket = Ticket::sole();
        $this->getJson('/dashboard/tickets/'.$ticket->id)->assertOk()->assertJsonPath('subject', 'Network issue');
        $this->postJson('/dashboard/tickets/'.$ticket->id.'/action', ['action_taken' => 'Troubleshooting the reported issue.', 'status' => 'In Progress', 'technician_id' => $technician->id])->assertOk();
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'technician_id' => $technician->id, 'status' => 'In Progress']);
        $this->get('/dashboard')->assertSee('data-ticket-url', false)->assertDontSee('href="'.route('tickets.create').'"', false);
    }

    public function test_general_branding_is_persisted_and_rendered(): void
    {
        $this->actingAs($this->staff())->postJson('/dashboard/settings/general', ['app_name' => 'Test Helpdesk', 'portal_subtitle' => 'Test Support Portal', 'support_tagline' => 'Test tagline', 'footer_credit' => 'Test IT Unit'])->assertOk();
        $this->get('/')->assertSee('Test Helpdesk')->assertSee('Test tagline')->assertSee('Test IT Unit');
    }

    public function test_profile_changes_require_current_password_and_do_not_allow_staff_privilege_fields(): void
    {
        $user = $this->staff();
        $this->actingAs($user)->postJson('/dashboard/settings/profile', ['name' => 'Updated Staff', 'email' => $user->email, 'username' => 'updated-staff', 'current_password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/dashboard/settings/profile', ['name' => 'Updated Staff', 'email' => $user->email, 'username' => 'updated-staff', 'current_password' => 'current-password-123', 'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password', 'is_staff' => false])->assertOk();
        $user->refresh();
        $this->assertTrue((bool) $user->is_staff);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-secure-password', $user->password));
    }

    public function test_profile_photo_is_validated_and_only_accessible_to_staff(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $this->actingAs($this->staff())->postJson('/dashboard/settings/technicians', [
            'name' => 'Photo Tech', 'email' => 'photo@example.com', 'is_active' => 1,
            'photo' => \Illuminate\Http\UploadedFile::fake()->createWithContent('profile.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX2kAAAAASUVORK5CYII=')),
        ])->assertOk();
        $technician = Technician::where('email', 'photo@example.com')->firstOrFail();
        $technician->update(['user_id' => auth()->id()]);
        $this->get('/dashboard')->assertOk()->assertSee('admin-avatar-photo')->assertSee(route('dashboard.technicians.photo', $technician), false);
        \Illuminate\Support\Facades\Storage::disk('local')->assertExists($technician->photo_path);
        $this->get('/dashboard/technicians/'.$technician->id.'/photo')->assertOk();
        $this->post('/logout');
        $this->get('/dashboard/technicians/'.$technician->id.'/photo')->assertRedirect('/login');
    }

    public function test_technician_account_can_be_created_reset_and_disabled(): void
    {
        $this->actingAs($this->staff());
        $data = ['name' => 'Access Technician', 'email' => 'access@example.com', 'is_active' => 1, 'user_access' => 1, 'username' => 'access-tech', 'password' => 'initial-password-123', 'password_confirmation' => 'initial-password-123'];
        $this->postJson('/dashboard/settings/technicians', $data)->assertOk();
        $technician = Technician::where('email', 'access@example.com')->firstOrFail();
        $user = $technician->user;
        $this->assertSame('access-tech', $user->username);
        $this->assertTrue((bool) $user->is_staff);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('initial-password-123', $user->password));
        $hash = $user->password;
        $data['password'] = $data['password_confirmation'] = '';
        $this->postJson('/dashboard/settings/technicians/'.$technician->id, $data)->assertOk();
        $this->assertSame($hash, $user->fresh()->password);
        $data['password'] = $data['password_confirmation'] = 'replacement-password-123';
        $this->postJson('/dashboard/settings/technicians/'.$technician->id, $data)->assertOk();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('replacement-password-123', $user->fresh()->password));
        $this->get('/dashboard/settings')->assertDontSee('replacement-password-123')->assertDontSee($hash);
        $data['password'] = $data['password_confirmation'] = '';
        $data['user_access'] = 0;
        $this->postJson('/dashboard/settings/technicians/'.$technician->id, $data)->assertOk();
        $this->assertFalse((bool) $user->fresh()->is_staff);
        $this->post('/logout');
        $this->post('/login', ['email' => 'access-tech', 'password' => 'replacement-password-123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_new_technician_access_requires_a_confirmed_password_and_self_disable_is_blocked(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff)->postJson('/dashboard/settings/technicians', ['name' => 'No Password', 'email' => 'missing@example.com', 'is_active' => 1, 'user_access' => 1, 'username' => 'missing-pass'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertDatabaseMissing('technicians', ['email' => 'missing@example.com']);
        $technician = Technician::create(['name' => $staff->name, 'email' => $staff->email, 'user_id' => $staff->id]);
        $this->postJson('/dashboard/settings/technicians/'.$technician->id, ['name' => $staff->name, 'email' => $staff->email, 'is_active' => 1, 'user_access' => 0])->assertUnprocessable()->assertJsonValidationErrors('user_access');
        $this->assertTrue((bool) $staff->fresh()->is_staff);
    }

    public function test_existing_technician_without_account_can_set_and_edit_short_dev_credentials(): void
    {
        config(['helpdesk.strict_passwords' => false]);
        $this->actingAs($this->staff());
        $tech = Technician::create(['name' => 'Existing Technician', 'email' => 'existing@example.com']);
        $data = ['name' => $tech->name, 'email' => $tech->email, 'is_active' => 1, 'user_access' => 1, 'username' => 'first-login', 'password' => '123', 'password_confirmation' => '123'];
        $this->postJson('/dashboard/settings/technicians/'.$tech->id, $data)->assertOk();
        $tech->refresh();
        $accountId = $tech->user_id;
        $this->assertNotNull($accountId);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('123', $tech->user->password));
        $data['username'] = 'edited-login';
        $data['password'] = $data['password_confirmation'] = '456';
        $this->postJson('/dashboard/settings/technicians/'.$tech->id, $data)->assertOk();
        $this->assertSame($accountId, $tech->fresh()->user_id);
        $this->assertSame('edited-login', $tech->fresh()->user->username);
        $this->post('/logout');
        $this->post('/login', ['email' => 'edited-login', 'password' => '456'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($tech->fresh()->user);
    }

    public function test_entered_credentials_are_saved_even_when_technician_access_is_disabled(): void
    {
        config(['helpdesk.strict_passwords' => false]);
        $this->actingAs($this->staff());
        $data = ['name' => 'Disabled Technician', 'email' => 'disabled@example.com', 'is_active' => 1, 'user_access' => 0, 'username' => 'disabled-tech', 'password' => '123', 'password_confirmation' => '123'];
        $this->postJson('/dashboard/settings/technicians', $data)->assertOk();
        $tech = Technician::where('email', $data['email'])->firstOrFail();
        $this->assertSame('disabled-tech', $tech->user->username);
        $this->assertFalse((bool) $tech->user->is_staff);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('123', $tech->user->password));
        $data['user_access'] = 1;
        $data['password'] = $data['password_confirmation'] = '';
        $this->postJson('/dashboard/settings/technicians/'.$tech->id, $data)->assertOk();
        $this->assertTrue((bool) $tech->fresh()->user->is_staff);
    }
}
