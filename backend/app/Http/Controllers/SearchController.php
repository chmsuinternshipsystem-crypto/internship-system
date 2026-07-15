<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function global(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 1) {
            return response()->json(['results' => []]);
        }

        $results = collect();

        // Search students
        $students = Student::query()
            ->where(function ($query) use ($q): void {
                $query->where('student_number', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhereRaw("CONCAT(last_name, ', ', first_name) LIKE ?", ["%{$q}%"]);
            })
            ->limit(5)
            ->get(['id', 'student_number', 'last_name', 'first_name'])
            ->map(fn ($s) => [
                'type' => 'student',
                'label' => "{$s->last_name}, {$s->first_name} ({$s->student_number})",
                'url' => route('students.show', $s),
                'icon' => 'bi-person',
            ]);

        $results = $results->concat($students);

        // Search companies
        if ($results->count() < 8) {
            $companies = Company::query()
                ->where('name', 'like', "%{$q}%")
                ->limit(5)
                ->get(['id', 'name'])
                ->map(fn ($c) => [
                    'type' => 'company',
                    'label' => $c->name,
                    'url' => route('companies.show', $c),
                    'icon' => 'bi-building',
                ]);
            $results = $results->concat($companies);
        }

        // Static page routes (only if query matches)
        $pages = [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bi-house-door'],
            ['key' => 'students', 'label' => 'Students', 'url' => route('students.index'), 'icon' => 'bi-people'],
            ['key' => 'companies', 'label' => 'Companies', 'url' => route('companies.index'), 'icon' => 'bi-building'],
            ['key' => 'deployments', 'label' => 'Deployments', 'url' => route('deployments.index'), 'icon' => 'bi-briefcase'],
            ['key' => 'compliance', 'label' => 'Compliance', 'url' => route('compliance.index'), 'icon' => 'bi-check-circle'],
            ['key' => 'attendance', 'label' => 'Attendance', 'url' => route('attendance.index'), 'icon' => 'bi-clock-history'],
            ['key' => 'queue', 'label' => 'Document Queue', 'url' => route('student-documents.queue'), 'icon' => 'bi-file-earmark-check'],
            ['key' => 'evaluations', 'label' => 'Evaluations', 'url' => route('evaluations.index'), 'icon' => 'bi-star'],
            ['key' => 'journals', 'label' => 'Weekly Journals', 'url' => route('weekly-journals.index'), 'icon' => 'bi-journal-text'],
            ['key' => 'dtr', 'label' => 'DTR', 'url' => route('dtr.index'), 'icon' => 'bi-calendar-week'],
            ['key' => 'certificates', 'label' => 'Certificates', 'url' => route('certificates.index'), 'icon' => 'bi-patch-check'],
            ['key' => 'announcements', 'label' => 'Announcements', 'url' => route('announcements.index'), 'icon' => 'bi-megaphone'],
            ['key' => 'messages', 'label' => 'Messages', 'url' => route('messages.index'), 'icon' => 'bi-chat-dots'],
            ['key' => 'reports', 'label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bi-file-earmark-bar-graph'],
            ['key' => 'archive', 'label' => 'Archive', 'url' => route('archive.index'), 'icon' => 'bi-archive'],
        ];

        $matchingPages = collect($pages)
            ->filter(fn ($p) => stripos($p['key'], $q) !== false || stripos($p['label'], $q) !== false)
            ->map(fn ($p) => [
                'type' => 'page',
                'label' => $p['label'],
                'url' => $p['url'],
                'icon' => $p['icon'],
            ]);

        $results = $results->concat($matchingPages)->take(10);

        return response()->json(['results' => $results]);
    }
}
