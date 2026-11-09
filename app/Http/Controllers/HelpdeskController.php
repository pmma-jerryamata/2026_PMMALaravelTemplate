<?php

namespace App\Http\Controllers;

use App\Mail\Mailer;
use App\Mail\MailerStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\RatingResult;
use App\Models\Support;
use App\Models\Task;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        $email = $request->old('email', $request->query('email', $request->query('emailProxy', '')));
        $email = is_string($email) ? $email : '';
        $emailValid = $email !== '' && Employee::where('email', $email)->exists();
        return $this->page('portal.create', [
            'services' => $this->services(), 'staff' => $staff, 'emailValid' => $emailValid,
            'email' => $email, 'selected' => (string) $request->query('category', $request->query('tasks1', '')),
            'submissionToken' => (string) Str::uuid(),
            'technicians' => $staff ? Technician::orderBy('name')->get() : collect(),
        ]);
    }

    public function requester(Request $request)
    {
        $data = $request->validate(['email' => 'required|email|max:255']);
        if (! Employee::where('email', $data['email'])->exists()) {
            return response()->json(['valid' => false, 'message' => 'This email is not registered. Please contact ITSU.'], 422)
                ->header('Cache-Control', 'private, no-store');
        }
        return response()->json(['valid' => true])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $staff = $request->routeIs('dashboard.tickets.store');
        $data = $request->validate([
            'email' => ['required', 'email', Rule::exists('employees', 'email')->whereNull('deleted_at')],
            'task_id' => 'required|integer|exists:tasks,id',
            'support_specifics' => 'required|string|max:10000',
            'submission_token' => 'required|uuid',
            'technician_id' => $staff ? 'nullable|integer|exists:technicians,id' : 'prohibited',
        ], ['email.exists' => 'Your email is not in the active PMMA directory. Please contact ITSU.']);
        $token = 'helpdesk.submitted.'.$data['submission_token'];
        if ($request->session()->has($token)) {
            return redirect()->route('tickets.receipt', ['ticket' => $request->session()->get($token)]);
        }
        $employee = Employee::where('email', $data['email'])->firstOrFail();
        $support = Support::create([
            'employee_id' => $employee->id, 'task_id' => $data['task_id'],
            'status' => 'Pending', 'support_specifics' => $data['support_specifics'],
            'technician_id' => $staff ? ($data['technician_id'] ?? null) : null, 'ip' => $request->ip(),
        ]);
        $request->session()->put($token, $support->id);
        $request->session()->put('helpdesk.receipts.'.$support->id, true);
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
        return $this->page('portal.receipt', compact('ticket'));
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
        abort_unless(in_array($type, ['employees', 'departments', 'technicians', 'services'], true), 404);
        return ['employees' => Employee::class, 'departments' => Department::class,
            'technicians' => Technician::class, 'services' => Task::class][$type];
    }

    public function settings(Request $request)
    {
        $type = $request->query('tab', 'employees');
        $model = $this->catalogModel($type);
        $query = $type === 'employees' ? Employee::withTrashed()->with('department') : $model::query();
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 150);
        if ($search !== '') { $query->where('name', 'like', '%'.$search.'%'); }
        return $this->page('dashboard.settings', [
            'type' => $type, 'search' => $search, 'records' => $query->orderBy('name')->paginate(20)->withQueryString(),
        ]);
    }

    public function editRecord($type, $id = null)
    {
        $model = $this->catalogModel($type);
        $record = $id ? ($type === 'employees' ? Employee::withTrashed()->findOrFail($id) : $model::findOrFail($id)) : new $model;
        return $this->page('dashboard.record', [
            'type' => $type, 'record' => $record, 'departments' => Department::orderBy('name')->get(),
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
        $data = $request->validate($rules);
        if ($type === 'employees') {
            $record->deleted_at = $data['active'] ? null : ($record->deleted_at ?? now());
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
