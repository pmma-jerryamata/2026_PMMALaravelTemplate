<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Technician;
use App\Support\DashboardReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DirectoryDeletionController extends Controller
{
    private function record(string $type, $id, bool $lock = false)
    {
        abort_unless(in_array($type, ['employees', 'technicians'], true), 404);
        $query = $type === 'employees'
            ? Employee::withTrashed()->where('activation_pending', true)->whereNotNull('deleted_at')
            : Technician::query();
        if ($lock) $query->lockForUpdate();
        return $query->findOrFail($id);
    }

    public function confirm(string $type, $id)
    {
        return view('helpdesk.dashboard.delete-directory-record', [
            'type' => $type, 'record' => $this->record($type, $id),
            'returnTo' => DashboardReturn::url(route('dashboard.settings', ['tab' => $type])),
        ]);
    }

    public function destroy(Request $request, string $type, $id)
    {
        $record = $this->record($type, $id);
        $request->validate([
            'confirmation' => ['required', Rule::in(['DELETE #'.$record->id])],
            'password' => ['required', 'string', 'current_password'],
            'acknowledge' => ['accepted'],
        ]);
        DB::transaction(function () use ($type, $id) {
            // Recheck eligibility under a lock: an activated user cannot be deleted by an old form.
            $record = $this->record($type, $id, true);
            $record->timestamps = false;
            $record->directory_archived_at = now();
            if ($type === 'employees') {
                $record->activation_pending = false;
                $record->updated_at = now();
            }
            $record->save();
        });
        return redirect(DashboardReturn::url(route('dashboard.settings', ['tab' => $type])))
            ->with('success', $record->name.' was removed from the directory. Existing ticket history was preserved.');
    }
}
