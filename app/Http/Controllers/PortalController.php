<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    public function home()
    {
        return view('portal.home', ['services' => \App\Models\ServiceCategory::catalog(), 'recentTickets' => Ticket::with(['department', 'latestAction', 'latestUpdate'])->select(['id', 'reference', 'subject', 'department_id', 'status', 'created_at'])->latest()->orderByDesc('id')->limit(10)->get()]);
    }

    public function timeline(string $reference)
    {
        $ticket = Ticket::where('reference', Str::upper($reference))->firstOrFail();
        return response()->json(['reference' => $ticket->reference, 'html' => view('portal.partials.timeline', ['updates' => $ticket->updates])->render()])->header('Cache-Control', 'no-store');
    }

    public function services(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $services = collect(\App\Models\ServiceCategory::catalog())->filter(fn ($service) => $query === '' || Str::contains(Str::lower($service['title'].' '.$service['description'].' '.implode(' ', $service['items'])), Str::lower($query)));
        return view('portal.services', compact('services', 'query'));
    }

    public function service(string $slug)
    {
        abort_unless(array_key_exists($slug, \App\Models\ServiceCategory::catalog()), 404);
        return view('portal.service', ['slug' => $slug, 'service' => \App\Models\ServiceCategory::catalog()[$slug]]);
    }

    public function create(Request $request)
    {
        return view('portal.create', ['services' => \App\Models\ServiceCategory::catalog(), 'selected' => $request->query('category'), 'departments' => Department::where('is_active', true)->directoryOrder()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where('is_active', true)],
            'category' => ['required', Rule::in(array_keys(\App\Models\ServiceCategory::catalog()))],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'priority' => ['required', Rule::in(['normal', 'high', 'urgent'])],
            'consent' => ['accepted'],
        ]);
        unset($data['consent']);
        $data['subject'] = Str::limit(Str::squish($data['description']), 197);
        $ticket = \Illuminate\Support\Facades\DB::transaction(fn () => Ticket::create($data + ['reference' => \App\Support\TicketReference::next((int) $data['department_id'])]));
        $request->attributes->set('audit_success', true);
        $request->attributes->set('audit_reference', $ticket->reference);
        if (!$request->user()) $request->attributes->set('audit_actor', ['id' => null, 'name' => $ticket->name.' (Requestee)', 'login' => $ticket->email]);
        // Clock polling and other requests can consume flash data before the redirect arrives.
        // Keep the latest receipt available only in the submitter's session, including refreshes.
        $request->session()->put('submitted_ticket', $ticket->reference);
        return redirect()->route('tickets.receipt');
    }

    public function receipt(Request $request)
    {
        abort_unless($request->session()->has('submitted_ticket'), 404);
        return view('portal.receipt', ['reference' => $request->session()->get('submitted_ticket')]);
    }

    public function track(Request $request)
    {
        $ticket = null;
        $searched = $request->isMethod('post');
        if ($searched) {
            $data = $request->validate(['reference' => ['required', 'string', 'max:80']]);
            $ticket = Ticket::with(['updates', 'department', 'technician'])->where('reference', Str::upper(trim($data['reference'])))->first();
        }
        return view('portal.track', compact('ticket', 'searched'));
    }
}
