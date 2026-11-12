<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\Technician;
use App\Services\Reports\AccomplishmentPdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function data(Request $request): array
    {
        $input = $request->validate([
            'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'technician_id' => 'nullable|integer|exists:technicians,id',
            'department_id' => 'nullable|integer|exists:departments,id', 'task_id' => 'nullable|integer|exists:tasks,id',
            'prepared_by' => 'nullable|string|max:100', 'prepared_position' => 'nullable|string|max:100',
            'noted_by' => 'nullable|string|max:100', 'noted_position' => 'nullable|string|max:100',
            'download' => 'nullable|boolean',
            'content_html' => 'nullable|string|max:12000',
            'content_default' => 'nullable|boolean',
        ]);
        $from = $input['from'] ?? now()->startOfMonth()->addDays(now()->day > 15 ? 15 : 0)->toDateString();
        $to = $input['to'] ?? now()->toDateString();
        if (Carbon::parse($to)->lt(Carbon::parse($from))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['to' => 'The end date must be on or after the start date.']);
        }
        $query = Support::with(['task', 'technician', 'employee' => fn ($employee) => $employee->withTrashed()->with('department')])
            ->where('status', 'Completed')
            ->whereBetween(DB::raw('COALESCE(updated_at, created_at)'), [$from.' 00:00:00', $to.' 23:59:59']);
        if (! empty($input['technician_id'])) { $query->where('technician_id', $input['technician_id']); }
        if (! empty($input['task_id'])) $query->where('task_id', $input['task_id']);
        if (! empty($input['department_id'])) $query->whereHas('employee', fn ($employee) => $employee->withTrashed()->where('department_id', $input['department_id']));
        $tickets = $query->orderByRaw('COALESCE(updated_at, created_at)')->orderBy('id')->get();
        $groups = $tickets->groupBy(fn ($ticket) => ($ticket->updated_at ?? $ticket->created_at)->toDateString())
            ->map(function ($dayTickets, $date) {
                return ['date' => Carbon::parse($date)->format('F j, Y'), 'items' => $dayTickets->map(function ($ticket) {
                    $description = trim($ticket->findings ?: $ticket->support_specifics ?: optional($ticket->task)->name ?: 'Completed support request');
                    return $description;
                })->unique(fn ($description) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($description))))->values()->all()];
            })->values()->all();
        $signatories = [];
        foreach (['prepared_by', 'prepared_position', 'noted_by', 'noted_position'] as $field) {
            // Signatory values belong to this export only; they are never saved to a database or configuration.
            $signatories[$field] = trim(preg_replace('/\s+/u', ' ', $input[$field] ?? ''));
        }
        $contentHtml = \App\Services\Reports\ReportContent::clean(! empty($input['content_default']) ? '' : ($input['content_html'] ?? ''));
        return compact('from', 'to', 'tickets', 'groups', 'signatories', 'contentHtml') + [
            'departments' => \App\Models\Department::orderBy('name')->get(), 'services' => \App\Models\Task::displayOrder()->get(),
            'departmentId' => $input['department_id'] ?? '', 'taskId' => $input['task_id'] ?? '',
            'technicianId' => $input['technician_id'] ?? '', 'technicians' => Technician::orderBy('name')->get(),
        ];
    }

    public function jobs(Request $request, \App\Services\Reports\JobRequestsSummary $summary)
    {
        return view('helpdesk.dashboard.report-jobs', $summary->data($request));
    }

    public function jobsPdf(Request $request, \App\Services\Reports\JobRequestsSummary $summary, \App\Services\Reports\JobRequestsPdf $document)
    {
        return response($document->render($summary->data($request)), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="job-requests-report.pdf"',
            'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function index(Request $request)
    {
        return view('helpdesk.dashboard.report', $this->data($request));
    }

    public function pdf(Request $request, AccomplishmentPdf $document)
    {
        $data = $this->data($request);
        $bytes = $document->render($data['groups'], $data['from'], $data['to'], $data['signatories'], $data['contentHtml']);
        $filename = 'accomplishment-report-'.$data['from'].'-to-'.$data['to'].'.pdf';
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline').'; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
