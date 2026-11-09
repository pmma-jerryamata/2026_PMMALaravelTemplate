<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Support\TicketReference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_office_has_a_unique_three_letter_code_and_independent_sequence(): void
    {
        $codes = [];
        foreach (Department::directoryOrder()->get() as $department) {
            $code = TicketReference::officeCode($department);
            $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $code, $department->name);
            $this->assertNotContains($code, $codes, 'Duplicate office code: '.$department->name);
            $codes[] = $code;
            $year = now()->format('Y');
            $this->assertSame("APP-$year-$code-000001", DB::transaction(fn () => TicketReference::next($department->id)));
            $this->assertSame("APP-$year-$code-000002", DB::transaction(fn () => TicketReference::next($department->id)));
        }
        $this->assertCount(45, $codes);
    }

    public function test_similar_office_names_have_distinct_codes(): void
    {
        foreach ([
            'BOA: Admissions Office' => 'BOA',
            'Board Secretary' => 'BSE',
            'OAS-Administration and Finance' => 'OAF',
            'OAS-ATRE' => 'ATR',
            'Training Center' => 'TRC',
            'Transportation Unit' => 'TRN',
        ] as $name => $code) {
            $this->assertSame($code, TicketReference::officeCode(Department::where('name', $name)->firstOrFail()));
        }
    }
}
