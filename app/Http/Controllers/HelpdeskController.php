<?php

namespace App\Http\Controllers;

use App\Mail\Mailer;
use App\Mail\MailerStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\RatingResult;
use App\Models\Support;
use App\Models\ServiceIssue;
use App\Models\Task;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Adapt the supplied helpdesk presentation to the existing database, without migrations. */
class HelpdeskController extends Controller
{
    public const STATUSES = ['Pending', 'Completed', 'Cancelled'];

    private function services()
    {
        return Task::orderBy('id')->get()->mapWithKeys(function ($task) {
            $name = strtolower($task->name);
            $icon = Str::contains($name, ['internet', 'network']) ? 'wifi'
                : (Str::contains($name, ['pc/', 'laptop']) ? 'laptop'
                : (Str::contains($name, 'printer') ? 'printer'
                : (Str::contains($name, 'software') ? 'window-stack'
                : (Str::contains($name, 'hardware') ? 'motherboard' : 'headset'))));
            if (preg_match('/(?:^|\s)bi-([a-z0-9-]+)/', $task->icon ?? '', $matches)) {
                $icon = $matches[1];
            }
            return [(string) $task->id => [
                'id' => $task->id, 'title' => $task->name, 'icon' => $icon,
                'description' => trim(strip_tags($task->content)),
                'items' => [trim(strip_tags($task->content)) ?: $task->name],
            ]];
        });
    }

    private function page($name, array $data = [])
    {
        return view('helpdesk.'.$name, $data);
    }

    private function tickets()
    {
        return Support::with(['task', 'technician', 'employee' => function ($query) {
            $query->withTrashed()->with('department');
        }]);
    }

    public function home()
    {
        return $this->page('portal.home', [
            'services' => $this->services(),
            'recentTickets' => $this->tickets()
                ->orderByDesc('created_at')->orderByDesc('id')->limit(10)->get(),
        ]);
    }

    public function servicesIndex(Request $request)
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 150);
        $services = $this->services()->filter(fn ($service) => $query === '' ||
            Str::contains(mb_strtolower($service['title'].' '.$service['description']), mb_strtolower($query)));
        return $this->page('portal.services', compact('query', 'services'));
    }

    public function service($slug)
    {
        $service = $this->services()->get($slug);
        abort_unless($service, 404);
        return $this->page('portal.service', compact('slug', 'service'));
    }

    public function create(Request $request)
    {
        $staff = $request->routeIs('dashboard.tickets.create', 'add');
        if (! $staff) {
            $step = $request->query('step', 'services');
            abort_unless(in_array($step, ['services', 'issue', 'email'], true), 404);
            $draft = $request->session()->get('helpdesk.draft', []);
            $serviceId = $step === 'email' ? ($draft['task_id'] ?? '') : $request->query('category', $draft['task_id'] ?? '');
            $service = $this->services()->get($serviceId);
            if ($step !== 'services' && ! $service) { return redirect()->route('tickets.create'); }
            if ($step === 'email' && empty($draft['support_specifics'])) {
                return redirect()->route('tickets.create', ['step' => 'issue', 'category' => $serviceId]);
            }
            return $this->page('portal.wizard', [
                'step' => $step, 'services' => $this->services(), 'service' => $service,
                'serviceId' => $serviceId, 'draft' => $draft,
                'issues' => $step === 'issue' && Schema::hasTable('service_issues')
                    ? ServiceIssue::where('task_id', $serviceId)->orderBy('name')->get() : collect(),
                'departments' => Department::orderBy('name')->get(['id', 'name']),
                'submissionToken' => $draft['submission_token'] ?? (string) Str::uuid(),
            ]);
        }
        $email = $request->old('email', $request->query('email', $request->query('emailProxy', '')));
        $email = is_string($email) ? $email : '';
        $emailValid = $email !== '' && Employee::where('email', $email)->exists();
        return $this->page('portal.create', [
            'services' => $this->services(), 'staff' => $staff, 'emailValid' => $emailValid,
            'email' => $email, 'selected' => (string) $request->query('category', $request->query('tasks1', '')),
            'submissionToken' => (string) Str::uuid(),
            'technicians' => $staff ? Technician::orderBy('name')->get() : collect(),
            'departments' => $staff ? Department::orderBy('name')->get(['id', 'name']) : collect(),
            'employees' => $staff ? Employee::orderBy('name')->get(['id', 'name', 'department_id']) : collect(),
        ]);
    }

    public function requester(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => ['required', 'email', 'max:255', 'regex:/^[^@]+@pmma\.edu\.ph$/i']]);
        $employee = Employee::withTrashed()->where('email', $data['email'])->first();
        if ($employee && $employee->trashed() && ! $employee->activation_pending) {
            return response()->json(['valid' => false, 'message' => 'This directory account is inactive. Please contact ITSU.'], 422)
                ->header('Cache-Control', 'private, no-store');
        }
        if (! $employee) {
            return response()->json(['valid' => true, 'registration_required' => true])
                ->header('Cache-Control', 'private, no-store');
        }
        return response()->json(['valid' => true])->header('Cache-Control', 'private, no-store');
    }

    public function ticketDetails(Request $request)
    {
        $data = $request->validate(['task_id' => 'required|integer|exists:tasks,id',
            'support_specifics' => 'required|string|max:10000']);
        $data['submission_token'] = (string) Str::uuid();
        $request->session()->put('helpdesk.draft', $data);
        return redirect()->route('tickets.create', ['step' => 'email']);
    }

    public function store(Request $request)
    {
        $registration = [];
        $staff = $request->routeIs('dashboard.tickets.store');
        if (! $staff) {
            $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        }
        $data = $request->validate([
            'email' => $staff ? 'exclude' : ['required', 'email', 'max:255', 'regex:/^[^@]+@pmma\.edu\.ph$/i'],
            'department_id' => $staff ? 'required|integer|exists:departments,id' : 'exclude',
            'employee_id' => $staff ? ['required', 'integer', Rule::exists('employees', 'id')
                ->whereNull('deleted_at')->where('department_id', $request->input('department_id'))] : 'exclude',
            'task_id' => 'required|integer|exists:tasks,id',
            'support_specifics' => 'required|string|max:10000',
            'submission_token' => 'required|uuid',
            'technician_id' => $staff ? 'nullable|integer|exists:technicians,id' : 'prohibited',
        ], ['email.exists' => 'Your email is not in the active PMMA directory. Please contact ITSU.']);
        $token = 'helpdesk.submitted.'.$data['submission_token'];
        if ($request->session()->has($token)) {
            return redirect()->route('tickets.receipt', ['ticket' => $request->session()->get($token)]);
        }
        if (! $staff) {
            $employee = Employee::withTrashed()->where('email', $data['email'])->first();
            if ($employee && $employee->trashed() && ! $employee->activation_pending) {
                throw \Illuminate\Validation\ValidationException::withMessages(['email' => 'This directory account is inactive. Please contact ITSU.']);
            }
            if (! $employee) {
                $registration = $request->validate(['name' => 'required|string|max:255',
                    'registration_department_id' => 'required|integer|exists:departments,id']);
            }
        }
        $support = DB::transaction(function () use ($staff, $data, $request, $registration) {
            $employee = $staff
                ? Employee::where('department_id', $data['department_id'])->findOrFail($data['employee_id'])
                : Employee::withTrashed()->where('email', $data['email'])->first();
            if (! $employee) {
                $employee = Employee::create(['email' => $data['email'], 'name' => mb_strtoupper(trim($registration['name'])),
                    'department_id' => $registration['registration_department_id'],
                    'deleted_at' => now(), 'activation_pending' => true, 'updated_at' => now()]);
            }
            return Support::create([
                'employee_id' => $employee->id, 'task_id' => $data['task_id'],
                'status' => 'Pending', 'support_specifics' => $data['support_specifics'],
                'technician_id' => $staff ? ($data['technician_id'] ?? null) : null, 'ip' => $request->ip(),
            ]);
        });
        $employee = Employee::withTrashed()->findOrFail($support->employee_id);
        $request->session()->put($token, $support->id);
        $request->session()->put('helpdesk.receipts.'.$support->id, true);
        if (! $staff) { $request->session()->forget('helpdesk.draft'); }
        // Keep the existing confirmation mail; transport failures must not create duplicate tickets.
        try {
            Mail::to($employee->email)->send(new Mailer($support->id));
        } catch (\Throwable $exception) {
            report($exception);
        }
        return $staff
            ? redirect()->route('dashboard.tickets.show', $support->id)->with('success', 'Ticket created successfully.')
            : redirect()->route('tickets.receipt', ['ticket' => $support->id]);
    }

    public function receipt(Request $request)
    {
        $id = $request->integer('ticket');
        abort_unless($request->session()->get('helpdesk.receipts.'.$id), 404);
        $ticket = Support::findOrFail($id);
        $activationPending = Employee::withTrashed()->whereKey($ticket->employee_id)->value('activation_pending');
        return $this->page('portal.receipt', compact('ticket', 'activationPending'));
    }

    public function track(Request $request)
    {
        $searched = $request->isMethod('post') || $request->filled('reference');
        $ticket = null;
        if ($searched) {
            $data = $request->validate(['reference' => 'required|integer|min:1']);
            $ticket = $this->tickets()->find($data['reference']);
        }
        return $this->page('portal.track', compact('searched', 'ticket'));
    }

    public function queue()
    {
        return $this->page('portal.queue', [
            'groups' => $this->tickets()->where('created_at', '>=', now()->subDays(7))
                ->orderByDesc('updated_at')->limit(90)->get()->groupBy('status'),
        ]);
    }

    public function dashboard(Request $request)
    {
        $query = $this->tickets();
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)], 'q' => 'nullable|string|max:150',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'technician_id' => 'nullable|integer|exists:technicians,id',
        ]);
        foreach (['status', 'task_id', 'technician_id'] as $field) {
            if (! empty($filters[$field])) { $query->where($field, $filters[$field]); }
        }
        if (! empty($filters['department_id'])) {
            $query->whereHas('employee', fn ($employee) => $employee->withTrashed()->where('department_id', $filters['department_id']));
        }
        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $query->where(function ($search) use ($term) {
                $search->where('support_specifics', 'like', '%'.$term.'%')
                    ->orWhere('id', $term)
                    ->orWhereHas('employee', fn ($employee) => $employee->withTrashed()
                        ->where(fn ($person) => $person->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%')));
            });
        }
        $counts = Support::select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->pluck('total', 'status');
        $services = $this->services();
        $analytics = app(\App\Services\DashboardAnalytics::class)->summarize($request);
        return $this->page('dashboard.index', [
            'tickets' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(15)->withQueryString(),
            'counts' => $counts, 'services' => $services, 'analytics' => $analytics,
            'departments' => Department::orderBy('name')->get(), 'technicians' => Technician::orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function show($ticket)
    {
        return $this->page('dashboard.ticket', [
            'ticket' => $this->tickets()->findOrFail($ticket),
            'technicians' => Technician::orderBy('name')->get(), 'statuses' => self::STATUSES,
        ]);
    }

    public function legacyEdit(Request $request)
    {
        $request->validate(['passValue' => 'required|integer|min:1']);
        return redirect()->route('dashboard.tickets.show', $request->integer('passValue'));
    }

    public function update(Request $request, $ticket)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'technician_id' => 'nullable|integer|exists:technicians,id', 'findings' => 'nullable|string|max:1023',
            'accomplished_at' => 'nullable|date',
        ]);
        $support = $this->tickets()->findOrFail($ticket);
        $support->status = $data['status'];
        $support->technician_id = $data['technician_id'] ?? null;
        $support->findings = $data['findings'] ?? null;
        $support->updated_at = $data['accomplished_at'] ?? now();
        $support->save();
        if ($support->status !== 'Pending' && $support->employee) {
            $support->load('technician');
            try {
                Mail::to($support->employee->email)->send(new MailerStatus(
                    $support->id, $support->status, $support->created_at, $support->employee->name,
                    optional($support->employee->department)->name, optional($support->task)->name,
                    $support->support_specifics, $support->findings, optional($support->technician)->name ?? 'Unassigned',
                    $support->updated_at
                ));
            } catch (\Throwable $exception) { report($exception); }
        }
        return redirect()->route('dashboard.tickets.show', $support->id)->with('success', 'Ticket updated successfully.');
    }

    public function confirmDelete($ticket)
    {
        return $this->page('dashboard.delete-ticket', [
            'ticket' => $this->tickets()->findOrFail($ticket),
        ]);
    }

    public function destroy(Request $request, $ticket)
    {
        $support = Support::findOrFail($ticket);
        $request->validate([
            'confirmation' => ['required', 'string', Rule::in(['DELETE #'.$support->id])],
            'password' => ['required', 'string', 'current_password'],
            'acknowledge' => ['accepted'],
        ], [
            'confirmation.in' => 'Type DELETE #'.$support->id.' exactly to confirm this ticket.',
            'acknowledge.accepted' => 'Confirm that you understand this deletion is permanent.',
        ]);

        DB::transaction(function () use ($support) {
            $locked = Support::whereKey($support->id)->lockForUpdate()->firstOrFail();
            RatingResult::where('support_id', $locked->id)->delete();
            $locked->delete();
        });

        return redirect()->route('dashboard')->with('success', 'Ticket #'.$support->id.' and its feedback were permanently deleted.');
    }

    private function catalogModel($type)
    {
        abort_unless(in_array($type, ['employees', 'departments', 'technicians', 'services', 'issues'], true), 404);
        return ['employees' => Employee::class, 'departments' => Department::class,
            'technicians' => Technician::class, 'services' => Task::class, 'issues' => ServiceIssue::class][$type];
    }

    public function settings(Request $request)
    {
        $type = $request->query('tab', 'employees');
        $model = $this->catalogModel($type);
        $query = $type === 'employees' ? Employee::withTrashed()->with('department') : $model::query();
        $activation = $type === 'employees' && $request->query('status') === 'activation';
        if ($activation) { $query->where('activation_pending', true)->whereNotNull('deleted_at'); }
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 150);
        if ($search !== '') { $query->where('name', 'like', '%'.$search.'%'); }
        return $this->page('dashboard.settings', [
            'type' => $type, 'search' => $search, 'activation' => $activation,
            'records' => $query->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function editRecord($type, $id = null)
    {
        $model = $this->catalogModel($type);
        $record = $id ? ($type === 'employees' ? Employee::withTrashed()->findOrFail($id) : $model::findOrFail($id)) : new $model;
        return $this->page('dashboard.record', [
            'type' => $type, 'record' => $record, 'departments' => Department::orderBy('name')->get(),
            'services' => $this->services(),
        ]);
    }

    public function saveRecord(Request $request, $type, $id = null)
    {
        $model = $this->catalogModel($type);
        $record = $id ? ($type === 'employees' ? Employee::withTrashed()->findOrFail($id) : $model::findOrFail($id)) : new $model;
        $rules = ['name' => 'required|string|max:255'];
        if (in_array($type, ['employees', 'technicians'], true)) {
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique($record->getTable(), 'email')->ignore($record->getKey())];
        }
        if ($type === 'employees') {
            $rules['department_id'] = 'required|integer|exists:departments,id';
            $rules['active'] = 'required|boolean';
        }
        if ($type === 'services') { $rules['content'] = 'required|string|max:255'; $rules['icon'] = 'required|string|max:255'; }
        if ($type === 'issues') { $rules['task_id'] = 'required|integer|exists:tasks,id'; }
        $data = $request->validate($rules);
        if ($type === 'employees') {
            $record->deleted_at = $data['active'] ? null : ($record->deleted_at ?? now());
            if ($data['active']) { $record->activation_pending = false; }
            $record->updated_at = now();
            unset($data['active']);
        }
        $record->timestamps = false;
        $record->fill($data);
        $record->save();
        return redirect()->route('dashboard.settings', ['tab' => $type])->with('success', 'Record saved successfully.');
    }

    public function report(Request $request)
    {
        return app(ReportController::class)->index($request);
    }

    public function surveyResults()
    {
        return $this->page('dashboard.surveys', [
            'results' => RatingResult::with(['rating', 'rating_measure', 'support.task'])->orderByDesc('id')->paginate(20),
        ]);
    }
}
