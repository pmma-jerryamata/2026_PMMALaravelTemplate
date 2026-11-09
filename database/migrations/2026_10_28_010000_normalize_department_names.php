<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $names = [
            'Students',
            'Accounting Unit',
            'Assessment Unit',
            'BAC: Bids and Awards Committee',
            'BMO: Business Management Office',
            'BOA: Admissions Office',
            'Board Secretary',
            'Budget Unit',
            'CAO: Chief Administrative Office',
            'Cashier Unit',
            'CME: College of Marine Engineering',
            'CMT: College of Marine Transportation',
            'COA: Commission on Audit',
            'DMA: Department of Midshipmen Affairs',
            'DNST: Dept. of Naval Science and Tactics',
            'Dormitory Unit',
            'DRDE: Dept. of Research and Development',
            'DST: Department of Shipboard Training',
            'Graduate School',
            'GSU: General Services Unit',
            'Guidance Unit',
            'HRMO: Human Resource Management Office',
            'Internal Audit',
            'IRO: International Relations Office',
            'ITSU: Info. Technology Services Unit',
            'Legal Unit',
            'Library Unit',
            'Medical and Dental Unit',
            'Mess Unit',
            'MSU: Maritime Simulation Unit',
            'OAS-Administration and Finance',
            'OAS-ATRE',
            'OOS: Office of the Superintendent',
            'PAMRU: Public Affairs and Media Relations Unit',
            'PDO: Project Development Office',
            'Planning Office',
            'QAO: Quality Assurance Office',
            'Records Unit, DPO',
            'Registrar Office',
            'Safety Unit',
            'Security Unit',
            'SPMU: Supply and Property Mngmt. Unit',
            'Training Center',
            'Transportation Unit',
            'Other Department / Office',
        ];

        DB::transaction(function () use ($names) {
            foreach ($names as $name) {
                DB::table('departments')->whereRaw('LOWER(name) = ?', [strtolower($name)])
                    ->update(['name' => $name, 'updated_at' => now()]);
            }
        });
    }

    public function down(): void
    {
        // Keep normalized labels and existing department references intact.
    }
};
