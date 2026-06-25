<?php

namespace App\Http\Controllers;

use App\Models\DocumentForwardingBatch;
use App\Models\DocumentForwardingItem;
use App\Models\StudentDocument;
use App\Models\TransmittalLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentForwardingController extends Controller
{
    public function index()
    {
        $batches = DocumentForwardingBatch::query()
            ->with(['creator', 'items.student', 'items.requiredDocument'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('document-forwarding.index', compact('batches'));
    }

    public function create()
    {
        $documents = StudentDocument::query()
            ->with(['student', 'requiredDocument'])
            ->whereIn('status', ['Submitted', 'Pending'])
            ->orderByDesc('submitted_at')
            ->limit(200)
            ->get();

        return view('document-forwarding.create', compact('documents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_document_ids' => ['required', 'array', 'min:1'],
            'student_document_ids.*' => ['required', 'integer', 'exists:student_documents,id'],
            'release_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $releaseAt = isset($validated['release_at']) ? Carbon::parse($validated['release_at']) : now();
        $isReleased = $releaseAt->lessThanOrEqualTo(now());

        DB::transaction(function () use ($validated, $releaseAt, $isReleased): void {
            $batch = DocumentForwardingBatch::query()->create([
                'created_by' => (int) auth()->id(),
                'release_at' => $releaseAt,
                'status' => $isReleased ? 'released' : 'scheduled',
                'notes' => $validated['notes'] ?? null,
            ]);

            $documents = StudentDocument::query()
                ->whereIn('id', array_map('intval', $validated['student_document_ids']))
                ->get();

            foreach ($documents as $doc) {
                $item = DocumentForwardingItem::query()->create([
                    'batch_id' => $batch->id,
                    'student_document_id' => $doc->id,
                    'student_id' => $doc->student_id,
                    'required_document_id' => $doc->required_document_id,
                    'released_at' => $isReleased ? now() : null,
                ]);

                if ($isReleased) {
                    TransmittalLog::query()->create([
                        'batch_id' => $batch->id,
                        'item_id' => $item->id,
                        'student_id' => $doc->student_id,
                        'required_document_id' => $doc->required_document_id,
                        'action_type' => 'forwarded',
                        'acted_by' => auth()->id(),
                        'acted_at' => now(),
                        'note' => 'Batch released for transmittal.',
                    ]);
                }
            }
        });

        return redirect()
            ->route('document-forwarding.index')
            ->with('status', __('Forwarding batch saved successfully.'));
    }

    public function release(DocumentForwardingBatch $batch)
    {
        if ($batch->status === 'released') {
            return back()->with('status', __('Batch already released.'));
        }

        DB::transaction(function () use ($batch): void {
            $batch->update([
                'status' => 'released',
                'release_at' => now(),
            ]);

            foreach ($batch->items as $item) {
                $item->update(['released_at' => now()]);

                TransmittalLog::query()->create([
                    'batch_id' => $batch->id,
                    'item_id' => $item->id,
                    'student_id' => $item->student_id,
                    'required_document_id' => $item->required_document_id,
                    'action_type' => 'forwarded',
                    'acted_by' => auth()->id(),
                    'acted_at' => now(),
                    'note' => 'Scheduled batch released.',
                ]);
            }
        });

        return back()->with('status', __('Batch released.'));
    }

    public function acknowledge(DocumentForwardingItem $item)
    {
        $item->update([
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        TransmittalLog::query()->create([
            'batch_id' => $item->batch_id,
            'item_id' => $item->id,
            'student_id' => $item->student_id,
            'required_document_id' => $item->required_document_id,
            'action_type' => 'acknowledged',
            'acted_by' => auth()->id(),
            'acted_at' => now(),
            'note' => 'Document receipt acknowledged.',
        ]);

        return back()->with('status', __('Receipt acknowledged.'));
    }
}
