<?php

namespace App\Http\Controllers;

use App\Models\Remark;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class RemarkController extends Controller
{
    /**
     * Store a new remark for the given student.
     */
    public function store(Request $request, Student $student)
    {
        Gate::authorize('create', Remark::class);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        Remark::create([
            'student_id' => $student->id,
            'author_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return redirect()
            ->route('students.show', $student)
            ->with('status', __('Remark added successfully.'))
            ->with('status_type', 'success');
    }
}
