<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Technician;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    private function models(): array { return ['departments' => Department::class, 'technicians' => Technician::class, 'services' => ServiceCategory::class]; }

    public function index()
    {
        return view('dashboard.settings', ['departments' => Department::directoryOrder()->get(), 'technicians' => Technician::with(['department', 'user:id,name,email,username,is_staff'])->get(), 'services' => ServiceCategory::all(), 'staffUsers' => \App\Models\User::where('is_staff', true)->get(['id', 'name'])]);
    }

    public function save(Request $request, string $type, ?int $id = null)
    {
        abort_unless(isset($this->models()[$type]), 404);
        $class = $this->models()[$type];
        $record = $id ? $class::findOrFail($id) : new $class;
        $rules = match ($type) {
            'departments' => ['name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($record->id)], 'code' => ['nullable', 'string', 'max:40', Rule::unique('departments')->ignore($record->id)]],
            'technicians' => ['name' => 'required|string|max:120', 'email' => ['required', 'email', 'max:254', Rule::unique('technicians')->ignore($record->id)], 'department_id' => 'nullable|integer|exists:departments,id', 'employee_number' => ['nullable', 'string', 'max:100', Rule::unique('technicians')->ignore($record->id)], 'phone' => 'nullable|string|max:40', 'position' => 'nullable|string|max:255', 'specialization' => 'nullable|string|max:255', 'bio' => 'nullable|string|max:5000'],
            'services' => ['slug' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('service_categories')->ignore($record->id)], 'title' => 'required|string|max:255', 'icon' => ['required', 'regex:/^[a-z0-9-]+$/', 'max:80'], 'description' => 'required|string|max:2000', 'items' => 'required|string|max:5000'],
        };
        $data = $request->validate($rules + ['is_active' => 'required|boolean']);
        if ($type === 'technicians') {
            return $this->saveTechnician($request, $record, $data);
        }
        if ($type === 'services') {
            if ($record->exists && $data['slug'] !== $record->slug && \App\Models\Ticket::where('category', $record->slug)->exists()) {
                return response()->json(['message' => 'The service key cannot change while tickets reference it.'], 422);
            }
            $data['items'] = array_values(array_filter(array_map('trim', preg_split('/\R/', $data['items']))));
        }
        $record->fill($data)->save();
        $request->attributes->set('audit_resource', ['type' => $type, 'id' => $record->id, 'is_active' => (bool) $record->is_active]);
        return response()->json(['message' => 'Settings saved successfully.']);
    }

    private function saveTechnician(Request $request, Technician $technician, array $data)
    {
        $user = $technician->user;
        abort_if($user?->is_dev_admin && !$request->user()->is_dev_admin, 403, 'Only Dev Admin can manage this account.');
        $access = $request->has('user_access') ? $request->boolean('user_access') : (bool) $user?->is_staff;
        if ($user?->id === $request->user()->id && !$access) {
            throw \Illuminate\Validation\ValidationException::withMessages(['user_access' => 'You cannot disable your own dashboard access.']);
        }
        // Explicitly entered credentials must be saved even when access is disabled.
        $saveAccount = $user || $access || $request->filled('username') || $request->filled('password');
        $credentials = $request->validate([
            'user_access' => 'sometimes|required|boolean',
            'username' => [($saveAccount ? 'required' : 'nullable'), 'alpha_dash:ascii', 'max:100', Rule::unique('users')->ignore($user?->id)],
            'password' => [($saveAccount && !$user ? 'required' : 'nullable'), 'string', \App\Support\PasswordPolicy::rule(), 'max:1024', 'confirmed'],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if ($saveAccount) {
            $request->validate(['email' => [Rule::unique('users')->ignore($user?->id)]]);
        }
        if ($access && !$data['is_active']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['user_access' => 'Activate the technician profile before enabling login access.']);
        }
        $oldPhoto = $technician->photo_path;
        $newPhoto = $request->hasFile('photo') ? $request->file('photo')->store('technician-photos', 'local') : null;
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $technician, $data, $credentials, $access, $user, $newPhoto, $saveAccount) {
                if ($saveAccount) {
                    $account = $user ?? new \App\Models\User;
                    $account->name = $data['name'];
                    $account->email = $data['email'];
                    if (array_key_exists('username', $credentials)) $account->username = $credentials['username'];
                    $account->is_staff = $access;
                    if ($request->filled('password')) {
                        $account->password = $credentials['password'];
                        $account->remember_token = \Illuminate\Support\Str::random(60);
                    }
                    $account->save();
                    $data['user_id'] = $account->id;
                    if (!$access || $request->filled('password')) {
                        \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $account->id)->where('id', '!=', $request->session()->getId())->delete();
                    }
                }
                if ($newPhoto) $data['photo_path'] = $newPhoto;
                $technician->fill($data)->save();
            });
        } catch (\Throwable $error) {
            if ($newPhoto) \Illuminate\Support\Facades\Storage::disk('local')->delete($newPhoto);
            throw $error;
        }
        if ($newPhoto && $oldPhoto) \Illuminate\Support\Facades\Storage::disk('local')->delete($oldPhoto);
        $request->attributes->set('audit_resource', ['type' => 'technicians', 'id' => $technician->id, 'user_id' => $technician->user_id, 'user_access' => $access, 'password_changed' => $request->filled('password')]);
        return response()->json(['message' => 'Technician profile and access saved successfully.']);
    }

    public function photo(Technician $technician)
    {
        abort_unless($technician->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($technician->photo_path), 404);
        return \Illuminate\Support\Facades\Storage::disk('local')->response($technician->photo_path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate(['name' => 'required|string|max:120', 'email' => ['required', 'email', 'max:254', Rule::unique('users')->ignore($user->id)], 'username' => ['nullable', 'alpha_dash:ascii', 'max:100', Rule::unique('users')->ignore($user->id)], 'current_password' => 'required|current_password', 'password' => ['nullable', 'string', \App\Support\PasswordPolicy::rule(), 'max:1024', 'confirmed']]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->username = $data['username'];
        if (!empty($data['password'])) $user->password = $data['password'];
        $user->save();
        $request->session()->regenerate();
        return response()->json(['message' => 'Profile updated successfully.']);
    }

    public function general(Request $request)
    {
        $data = $request->validate(['app_name' => 'required|string|max:80', 'portal_subtitle' => 'required|string|max:100', 'support_tagline' => 'required|string|max:200', 'footer_credit' => 'required|string|max:255']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) \App\Models\SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        });
        return response()->json(['message' => 'System settings updated successfully.']);
    }
}
