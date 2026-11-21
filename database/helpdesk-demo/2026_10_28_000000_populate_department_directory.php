<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $names = [
            'Students',
            'ACCOUNTING UNIT',
            'ASSESSMENT UNIT',
            'BAC: BIDS AND AWARDS COMMITTEE',
            'BMO: BUSINESS MANAGEMENT OFFICE',
            'BOA: ADMISSIONS OFFICE',
            'BOARD SECRETARY',
            'BUDGET UNIT',
            'CAO: CHIEF ADMINISTRATIVE OFFICE',
            'CASHIER UNIT',
            'CME: COLLEGE OF MARINE ENGINEERING',
            'CMT: COLLEGE OF MARINE TRANSPORTATION',
            'COA: COMMISSION ON AUDIT',
            'DMA: DEPARTMENT OF MIDSHIPMEN AFFAIRS',
            'DNST: DEPT. OF NAVAL SCIENCE AND TACTICS',
            'DORMITORY UNIT',
            'DRDE: DEPT. OF RESEARCH AND DEVELOPMENT',
            'DST: DEPARTMENT OF SHIPBOARD TRAINING',
            'GRADUATE SCHOOL',
            'GSU: GENERAL SERVICES UNIT',
            'GUIDANCE UNIT',
            'HRMO: HUMAN RESOURCE MANAGEMENT OFFICE',
            'INTERNAL AUDIT',
            'IRO: INTERNATIONAL RELATIONS OFFICE',
            'ITSU: INFO. TECHNOLOGY SERVICES UNIT',
            'LEGAL UNIT',
            'LIBRARY UNIT',
            'MEDICAL AND DENTAL UNIT',
            'MESS UNIT',
            'MSU: MARITIME SIMULATION UNIT',
            'OAS-ADMINISTRATION AND FINANCE',
            'OAS-ATRE',
            'OOS: OFFICE OF THE SUPERINTENDENT',
            'PAMRU: PUBLIC AFFAIRS AND MEDIA RELATIONS UNIT',
            'PDO: PROJECT DEVELOPMENT OFFICE',
            'PLANNING OFFICE',
            'QAO: QUALITY ASSURANCE OFFICE',
            'RECORDS UNIT, DPO',
            'REGISTRAR OFFICE',
            'SAFETY UNIT',
            'SECURITY UNIT',
            'SPMU: SUPPLY AND PROPERTY MNGMT. UNIT',
            'TRAINING CENTER',
            'TRANSPORTATION UNIT',
        ];

        DB::transaction(function () use ($names) {
            foreach ($names as $name) {
                $code = str_contains($name, ':') ? explode(':', $name, 2)[0] : null;
                $existing = DB::table('departments')->where('name', $name)->first();
                if (!$existing && $code) {
                    $existing = DB::table('departments')->where('code', $code)->first();
                }
                if ($existing) {
                    // Preserve IDs already referenced by tickets and technician profiles.
                    DB::table('departments')->where('id', $existing->id)->update(['name' => $name, 'updated_at' => now()]);
                } else {
                    DB::table('departments')->insert([
                        'name' => $name, 'code' => $code, 'is_active' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Directory records may be referenced by tickets; retain them on rollback.
    }
};
