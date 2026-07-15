<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChairpersonNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_chairperson_can_open_core_staff_modules(): void
    {
        $chair = User::factory()->create(['role' => 'chairperson']);

        // Master required-document catalog is instructor-only; chair uses Compliance + workflow queue instead.
        foreach ([
            route('dashboard'),
            route('students.index'),
            route('companies.index'),
            route('deployments.index'),
            route('compliance.index'),
            route('student-documents.queue'),
            route('attendance.index'),
            route('evaluations.index'),
            route('announcements.index'),
            route('announcements.create'),
            route('messages.index'),
            route('reports.index'),
        ] as $url) {
            $response = $this->actingAs($chair)->get($url);
            $this->assertTrue(
                $response->isOk(),
                'Expected 200 for '.$url.' but received '.$response->status().'.'
            );
        }
    }
}
