<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public const STATUSES = ['Open', 'In Progress', 'Resolved', 'Closed'];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $tickets = Ticket::with(['department', 'technician', 'latestAction', 'latestUpdate'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['department_id'] ?? null, fn ($query, $department) => $query->where('department_id', $department))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['q'] ?? null, fn ($query, $q) => $query->where(fn ($query) => $query->where('reference', 'like', '%'.$q.'%')->orWhere('subject', 'like', '%'.$q.'%')->orWhere('name', 'like', '%'.$q.'%')))
            ->latest()->limit(10)->get();
        $counts = Ticket::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('dashboard.index', ['analytics' => empty($filters['status']) ? app(\App\Services\TicketAnalytics::class)->build($filters['period'] ?? 'month', $filters) : ['period' => $filters['period'] ?? 'month'], 'tickets' => $tickets, 'counts' => $counts, 'statuses' => self::STATUSES, 'technicianCount' => Technician::where('is_active', true)->count(), 'departments' => \App\Models\Department::directoryOrder()->get(), 'technicians' => Technician::orderBy('name')->get(), 'services' => \App\Models\ServiceCategory::orderBy('id')->get()->keyBy('slug')->toArray()]);
    }

    private function filters(Request $request): array
    {
        return $request->validate(['q' => 'nullable|string|max:200', 'status' => ['nullable', Rule::in(self::STATUSES)],
            'department_id' => 'nullable|integer|exists:departments,id', 'category' => 'nullable|string|exists:service_categories,slug',
            'period' => ['nullable', Rule::in(['day', 'week', 'month'])]]);
    }

    public function analytics(Request $request)
    {
        $filters = $this->filters($request);
        return response()->json(app(\App\Services\TicketAnalytics::class)->build($filters['period'] ?? 'month', $filters))->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, Ticket $ticket)
    {
        if ($request->expectsJson()) return response()->json($ticket->toArray() + ['action_url' => route('dashboard.tickets.action', $ticket), 'timeline_url' => route('dashboard.tickets.timeline', $ticket)]);
        return view('dashboard.ticket', ['ticket' => $ticket->load(['department', 'technician']), 'technicians' => Technician::where('is_active', true)->orderBy('name')->get(), 'statuses' => self::STATUSES]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate($this->ticketRules(false, $ticket) + ['status' => 'prohibited', 'technician_id' => 'prohibited', 'action_taken' => 'prohibited']);
        \Illuminate\Support\Facades\DB::transaction(fn () => $ticket->update($data));
        $request->attributes->set('audit_reference', $ticket->reference);
        $request->attributes->set('audit_resource', ['id' => $ticket->id, 'status' => $ticket->status, 'technician_id' => $ticket->technician_id, 'department_id' => $ticket->department_id, 'category' => $ticket->category, 'priority' => $ticket->priority]);
        $request->attributes->set('audit_success', true);
        if ($request->expectsJson()) return response()->json(['message' => 'Ticket updated successfully.']);
        return back()->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Request $request, Ticket $ticket)
    {
        $request->attributes->set('audit_reference', $ticket->reference);
        $request->attributes->set('audit_resource', ['id' => $ticket->id]);
        \Illuminate\Support\Facades\DB::transaction(fn () => $ticket->delete());
        $request->attributes->set('audit_success', true);
        return response()->json(['message' => 'Ticket deleted successfully.']);
    }

    public function action(Request $request, Ticket $ticket)
    {
        $data = $request->validate(['status' => ['required', Rule::in(self::STATUSES)], 'technician_id' => ['nullable', 'integer', Rule::exists('technicians', 'id')->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $ticket->technician_id))], 'action_taken' => [Rule::requiredIf($request->input('status') !== 'Closed'), 'nullable', 'string', 'min:3', 'max:5000']]);
        if ($data['status'] === 'In Progress' && empty($data['technician_id'])) throw \Illuminate\Validation\ValidationException::withMessages(['technician_id' => 'Assign a technician before marking the ticket In Progress.']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($ticket, $data, $request) {
            $locked = Ticket::lockForUpdate()->findOrFail($ticket->id);
            $locked->actionTaken = $data['action_taken'] ?? null;
            $locked->fill(['status' => $data['status'], 'technician_id' => $data['technician_id'] ?? null]);
            if ($locked->isDirty()) $locked->save();
            elseif ($locked->actionTaken) {
                $locked->updates()->create(['user_id' => $request->user()->id, 'actor_name' => $request->user()->name, 'event' => 'action', 'summary' => 'Technician action recorded.', 'action_taken' => $data['action_taken']]);
                $locked->actionTaken = null;
            }
            if (!$locked->wasChanged()) $locked->touch();
        });
        $request->attributes->set('audit_reference', $ticket->reference);
        $request->attributes->set('audit_success', true);
        if ($request->expectsJson()) return response()->json(['message' => 'Ticket action and timeline updated successfully.']);
        return back()->with('success', 'Ticket action and timeline updated successfully.');
    }

    public function timeline(Ticket $ticket)
    {
        return response()->json(['reference' => $ticket->reference, 'html' => view('portal.partials.timeline', ['updates' => $ticket->updates])->render()])->header('Cache-Control', 'private, no-store');
    }

    private function ticketRules(bool $creating, ?Ticket $ticket = null): array
    {
        $required = $creating ? 'required' : 'sometimes';
        return [
            'name' => [$required, 'required', 'string', 'max:120'], 'email' => [$required, 'required', 'email', 'max:254'],
            'department_id' => [$required, 'required', 'integer', Rule::exists('departments', 'id')->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $ticket?->department_id))],
            'category' => [$required, 'required', Rule::in(array_merge(array_keys(\App\Models\ServiceCategory::catalog()), $ticket ? [$ticket->category] : []))],
            'subject' => [$required, 'required', 'string', 'max:200'], 'description' => [$required, 'required', 'string', 'min:20', 'max:10000'],
            'priority' => [$required, 'required', Rule::in(['normal', 'high', 'urgent'])],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->ticketRules(true));
        [$ticket, $created] = app(\App\Support\SubmissionGuard::class)->run($request, 'staff-tickets', Ticket::class, fn () => Ticket::create($data + ['reference' => \App\Support\TicketReference::next((int) $data['department_id'])]));
        $request->attributes->set('audit_reference', $ticket->reference);
        return response()->json(['message' => $created ? 'Ticket created successfully.' : 'Ticket already submitted.', 'reference' => $ticket->reference]);
    }
}
