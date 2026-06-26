<?php

namespace App\Http\Controllers;

use App\Models\EvaluationCriterion;
use App\Models\Setting;
use App\Models\User;
use App\Services\GeofencingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CampusSettingController extends Controller
{
    public function edit()
    {
        $campus = Setting::campus();
        $evaluationCriteria = EvaluationCriterion::query()
            ->orderBy('category_key')
            ->orderBy('sort_order')
            ->get();
        $instructors = User::where('role', 'instructor')->orderBy('name')->get(['id', 'name', 'email']);

        return view('settings.campus', compact('campus', 'evaluationCriteria', 'instructors'));
    }

    public function update(Request $request)
    {
        $campus = Setting::campus();

        $section = $request->input('section', 'all');

        $response = match ($section) {
            'boundary' => $this->updateBoundary($request, $campus),
            'geofence' => $this->updateGeofence($request, $campus),
            'attendance' => $this->updateAttendance($request, $campus),
            'policy' => $this->updatePolicy($request, $campus),
            'evaluation_criteria' => $this->updateEvaluationCriteria($request),
            'instructors' => $this->updateInstructors($request),
            default => $this->updateAll($request, $campus),
        };

        if ($response) {
            return $response;
        }

        return redirect()
            ->route('settings.campus.edit')
            ->with('status', __('Settings updated successfully.'));
    }

    private function updateBoundary(Request $request, Setting $campus): void
    {
        $data = $request->validate([
            'campus_boundary_buffer_meters' => ['required', 'integer', 'min:5', 'max:100'],
            'campus_boundary' => ['nullable', 'json'],
        ]);

        $campus->update($data);

        $boundaryJson = $data['campus_boundary'] ?? null;
        if ($boundaryJson && $boundaryJson !== '[]') {
            $vertices = json_decode($boundaryJson, true);
            if (is_array($vertices) && count($vertices) >= 3) {
                $wkt = GeofencingService::verticesToPolygonWkt($vertices);
                DB::statement("UPDATE settings SET campus_boundary = ST_GeomFromText(?) WHERE id = ?", [$wkt, $campus->id]);
            }
        } else {
            DB::statement("UPDATE settings SET campus_boundary = NULL WHERE id = ?", [$campus->id]);
        }
    }

    private function updateGeofence(Request $request, Setting $campus): void
    {
        $data = $request->validate([
            'campus_lat' => ['required', 'numeric', 'between:-90,90'],
            'campus_lng' => ['required', 'numeric', 'between:-180,180'],
            'campus_radius_meters' => ['required', 'integer', 'min:50', 'max:2000'],
        ]);

        $campus->update($data);
    }

    private function updateAttendance(Request $request, Setting $campus): ?\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'attendance_am_time_in_start' => ['required', 'date_format:H:i'],
            'attendance_am_time_in_end' => ['required', 'date_format:H:i'],
            'attendance_am_time_out_start' => ['required', 'date_format:H:i'],
            'attendance_am_time_out_end' => ['required', 'date_format:H:i'],
            'attendance_pm_time_in_start' => ['required', 'date_format:H:i'],
            'attendance_pm_time_in_end' => ['required', 'date_format:H:i'],
            'attendance_pm_time_out_start' => ['required', 'date_format:H:i'],
            'attendance_pm_time_out_end' => ['required', 'date_format:H:i'],
            'attendance_grace_minutes' => ['required', 'integer', 'min:0', 'max:180'],
        ]);

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'attendance_') && (str_ends_with($key, '_start') || str_ends_with($key, '_end'))) {
                $data[$key] = substr($value, 0, 5);
            }
        }

        $redirect = $this->validateTimeWindows($data);
        if ($redirect) {
            return $redirect;
        }

        $campus->update($data);

        return null;
    }

    private function validateTimeWindows(array $data): ?\Illuminate\Http\RedirectResponse
    {
        $ranges = [
            'attendance_am_time_in' => __('AM Clock In'),
            'attendance_am_time_out' => __('AM Clock Out'),
            'attendance_pm_time_in' => __('PM Clock In'),
            'attendance_pm_time_out' => __('PM Clock Out'),
        ];

        foreach ($ranges as $prefix => $label) {
            $start = $data[$prefix . '_start'] ?? null;
            $end = $data[$prefix . '_end'] ?? null;
            if ($start && $end && $start >= $end) {
                return redirect()
                    ->route('settings.campus.edit')
                    ->withErrors([$prefix . '_end' => __(':label start must be before end.', ['label' => $label])])
                    ->withInput();
            }
        }

        return null;
    }

    private function updatePolicy(Request $request, Setting $campus): ?\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'semester' => ['nullable', 'string', 'max:50'],
            'academic_year_start' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'academic_year_end' => ['nullable', 'integer', 'min:2021', 'max:2036'],
            'policy_review_notes' => ['nullable', 'string', 'max:512'],
            'maintenance_mode' => ['sometimes', 'boolean'],
        ]);

        $data['maintenance_mode'] = $request->has('maintenance_mode');
        $data['policy_review_notes'] = isset($data['policy_review_notes']) ? trim(strip_tags($data['policy_review_notes'])) : null;
        $data['semester'] = isset($data['semester']) ? trim(strip_tags($data['semester'])) : null;

        $start = $data['academic_year_start'] ?? null;
        $end = $data['academic_year_end'] ?? null;
        $data['academic_year'] = null;
        if ($start && $end) {
            if ((int) $end !== (int) $start + 1) {
                return redirect()
                    ->route('settings.campus.edit')
                    ->withErrors(['academic_year' => __('Academic year must be consecutive (e.g. 2025-2026).')])
                    ->withInput();
            }
            $data['academic_year'] = $start . '-' . $end;
        }

        unset($data['academic_year_start'], $data['academic_year_end']);

        $campus->update($data);

        return null;
    }

    private function updateEvaluationCriteria(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'criteria' => ['required', 'array'],
            'criteria.*.id' => ['required', 'integer', 'exists:evaluation_criteria,id'],
            'criteria.*.is_active' => ['sometimes', 'boolean'],
            'criteria.*.item_label' => ['required', 'string', 'max:255'],
        ]);

        foreach ($data['criteria'] as $item) {
            $criterion = EvaluationCriterion::find((int) $item['id']);
            if ($criterion) {
                $criterion->update([
                    'is_active' => isset($item['is_active']),
                    'item_label' => trim(strip_tags($item['item_label'])),
                ]);
            }
        }

        if ($request->has('new_criteria')) {
            $newData = $request->validate([
                'new_criteria' => ['required', 'array'],
                'new_criteria.*.category_key' => ['required', 'string', 'max:50'],
                'new_criteria.*.item_label' => ['required', 'string', 'max:255'],
            ]);

            foreach ($newData['new_criteria'] as $entry) {
                $catKey = trim(strip_tags($entry['category_key']));
                $existing = EvaluationCriterion::where('category_key', $catKey)->first();
                $categoryLabel = $existing ? $existing->category_label : Str::title(str_replace('_', ' ', $catKey));
                $itemLabel = trim(strip_tags($entry['item_label']));
                $itemKey = Str::snake($itemLabel);
                $maxOrder = EvaluationCriterion::where('category_key', $catKey)->max('sort_order') ?? 0;

                EvaluationCriterion::create([
                    'category_key' => $catKey,
                    'category_label' => $categoryLabel,
                    'item_key' => $itemKey,
                    'item_label' => $itemLabel,
                    'sort_order' => $maxOrder + 1,
                    'is_active' => true,
                ]);
            }
        }

        EvaluationCriterion::flushCriteriaCache();

        return null;
    }

    private function updateInstructors(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:120', 'regex:/^(?!.*\\d)\p{L}+(?:[ .\'-]\p{L}+)*$/u'],
            'first_name' => ['required', 'string', 'max:120', 'regex:/^(?!.*\\d)\p{L}+(?:[ .\'-]\p{L}+)*$/u'],
            'middle_name' => ['nullable', 'string', 'max:120', 'regex:/^(?!.*\\d)\p{L}+(?:[ .\'-]\p{L}+)*$/u'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'last_name' => trim(strip_tags($data['last_name'])),
            'first_name' => trim(strip_tags($data['first_name'])),
            'middle_name' => isset($data['middle_name']) ? trim(strip_tags($data['middle_name'])) : null,
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => 'instructor',
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('settings.campus.edit')
            ->with('status', __('Instructor :name created successfully.', ['name' => $user->name]))
            ->with('status_type', 'success');
    }

    private function updateAll(Request $request, Setting $campus): ?\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'campus_lat' => ['required', 'numeric', 'between:-90,90'],
            'campus_lng' => ['required', 'numeric', 'between:-180,180'],
            'campus_radius_meters' => ['required', 'integer', 'min:50', 'max:2000'],
            'campus_boundary_buffer_meters' => ['required', 'integer', 'min:5', 'max:100'],
            'campus_boundary' => ['nullable', 'json'],
            'attendance_am_time_in_start' => ['required', 'date_format:H:i'],
            'attendance_am_time_in_end' => ['required', 'date_format:H:i'],
            'attendance_am_time_out_start' => ['required', 'date_format:H:i'],
            'attendance_am_time_out_end' => ['required', 'date_format:H:i'],
            'attendance_pm_time_in_start' => ['required', 'date_format:H:i'],
            'attendance_pm_time_in_end' => ['required', 'date_format:H:i'],
            'attendance_pm_time_out_start' => ['required', 'date_format:H:i'],
            'attendance_pm_time_out_end' => ['required', 'date_format:H:i'],
            'attendance_grace_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'maintenance_mode' => ['sometimes', 'boolean'],
            'policy_review_notes' => ['nullable', 'string', 'max:512'],
            'semester' => ['nullable', 'string', 'max:50'],
            'academic_year_start' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'academic_year_end' => ['nullable', 'integer', 'min:2021', 'max:2036'],
        ]);

        $data['maintenance_mode'] = $request->has('maintenance_mode');
        $data['policy_review_notes'] = isset($data['policy_review_notes']) ? trim(strip_tags($data['policy_review_notes'])) : null;
        $data['semester'] = isset($data['semester']) ? trim(strip_tags($data['semester'])) : null;

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'attendance_') && (str_ends_with($key, '_start') || str_ends_with($key, '_end'))) {
                $data[$key] = is_string($value) ? substr($value, 0, 5) : $value;
            }
        }

        $start = $data['academic_year_start'] ?? null;
        $end = $data['academic_year_end'] ?? null;
        $data['academic_year'] = null;
        if ($start && $end) {
            if ((int) $end !== (int) $start + 1) {
                return redirect()
                    ->route('settings.campus.edit')
                    ->withErrors(['academic_year' => __('Academic year must be consecutive (e.g. 2025-2026).')])
                    ->withInput();
            }
            $data['academic_year'] = $start . '-' . $end;
        }

        unset($data['academic_year_start'], $data['academic_year_end']);

        $redirect = $this->validateTimeWindows($data);
        if ($redirect) {
            return $redirect;
        }

        $campus->update($data);

        $boundaryJson = $data['campus_boundary'] ?? null;
        if ($boundaryJson && $boundaryJson !== '[]') {
            $vertices = json_decode($boundaryJson, true);
            if (is_array($vertices) && count($vertices) >= 3) {
                $wkt = GeofencingService::verticesToPolygonWkt($vertices);
                DB::statement("UPDATE settings SET campus_boundary = ST_GeomFromText(?) WHERE id = ?", [$wkt, $campus->id]);
            }
        } else {
            DB::statement("UPDATE settings SET campus_boundary = NULL WHERE id = ?", [$campus->id]);
        }

        return null;
    }
}
