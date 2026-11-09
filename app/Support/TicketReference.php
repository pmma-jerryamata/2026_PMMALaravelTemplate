<?php

namespace App\Support;

use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketReference
{
    public static function officeCode(Department $department): string
    {
        // Distinguish offices whose names/acronyms start with the same three letters.
        $overrides = [
            'board secretary' => 'BSE',
            'oas-administration and finance' => 'OAF',
            'oas-atre' => 'ATR',
            'training center' => 'TRC',
            'transportation unit' => 'TRN',
        ];
        $name = Str::lower(trim($department->name));
        if (isset($overrides[$name])) {
            return $overrides[$name];
        }

        $letters = preg_replace('/[^A-Z]/', '', Str::upper(Str::ascii($department->code ?: $department->name)));
        return substr(str_pad($letters, 3, 'X'), 0, 3);
    }

    public static function next(int $departmentId): string
    {
        $department = Department::findOrFail($departmentId);
        $office = self::officeCode($department);
        $key = ['year' => (int) now()->format('Y'), 'office' => $office];

        // Call inside the ticket transaction so failed submissions do not consume numbers.
        DB::table('ticket_reference_counters')->insertOrIgnore($key + ['last_number' => 0]);
        $counter = DB::table('ticket_reference_counters')->where($key)->lockForUpdate()->first();
        $number = $counter->last_number + 1;
        DB::table('ticket_reference_counters')->where($key)->update(['last_number' => $number]);

        return sprintf(config('branding.reference_prefix').'-%d-%s-%06d', $key['year'], $office, $number);
    }
}
