<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequiredDocumentRequest;
use App\Http\Requests\UpdateRequiredDocumentRequest;
use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Models\RequiredDocument;
use App\Support\RequiredDocumentOrdering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequiredDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        RequiredDocumentOrdering::normalizeSequence();

        $query = RequiredDocument::orderBy('order_index')->orderBy('name');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('phase', 'like', "%{$search}%");
            });
        }

        $requiredDocuments = $query->paginate(5)->withQueryString();

        $canManage = in_array(auth()->user()->role ?? null, \App\Support\InternshipRoles::operationalManagerRoles(), true);

        if ($request->header('HX-Request')) {
            return view('required-documents.partials.ajax-list', compact('requiredDocuments', 'canManage'));
        }

        return view('required-documents.index', compact('requiredDocuments', 'canManage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $orderSlotChoices = RequiredDocumentOrdering::slotChoices(null);
        $companies = Company::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('required-documents.create', compact('orderSlotChoices', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequiredDocumentRequest $request)
    {
        $data = $request->validated();
        $data['is_mandatory'] = true;

        $orderSlot = $data['order_slot'] ?? null;
        unset($data['order_slot']);
        RequiredDocumentOrdering::saveAtSlot(null, $data, $orderSlot);
        RequiredDocument::flushCache();

        return redirect()
            ->to(route('required-documents.index') . ($request->input('page') ? '?page=' . (int) $request->input('page') : ''))
            ->with('status', __('Required document created successfully.'))
            ->with('status_type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(RequiredDocument $requiredDocument)
    {
        return view('required-documents.show', compact('requiredDocument'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequiredDocument $requiredDocument)
    {
        RequiredDocumentOrdering::normalizeSequence();
        $orderSlotChoices = RequiredDocumentOrdering::slotChoices($requiredDocument);
        $companies = Company::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('required-documents.edit', compact('requiredDocument', 'orderSlotChoices', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequiredDocumentRequest $request, RequiredDocument $requiredDocument)
    {
        $data = $request->validated();
        $data['is_mandatory'] = true;

        $orderSlot = $data['order_slot'] ?? null;
        unset($data['order_slot']);
        RequiredDocumentOrdering::saveAtSlot($requiredDocument, $data, $orderSlot);
        RequiredDocument::flushCache();

        return redirect()
            ->to(route('required-documents.index') . ($request->input('page') ? '?page=' . (int) $request->input('page') : ''))
            ->with('status', __('Required document updated successfully.'))
            ->with('status_type', 'success');
    }

    public function uploadTemplate(Request $request, RequiredDocument $requiredDocument)
    {
        $data = $request->validate([
            'template_file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $oldTemplate = DocumentTemplate::where('required_document_id', $requiredDocument->id)->first();
        if ($oldTemplate) {
            Storage::disk('public')->delete($oldTemplate->file_path);
            $oldTemplate->delete();
        }

        $path = $request->file('template_file')->store('templates', 'public');

        DocumentTemplate::create([
            'required_document_id' => $requiredDocument->id,
            'file_path' => $path,
            'original_name' => $request->file('template_file')->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('required-documents.index')
            ->with('status', __('Template uploaded successfully.'))
            ->with('status_type', 'success');
    }

    public function downloadTemplate(RequiredDocument $requiredDocument)
    {
        $template = DocumentTemplate::where('required_document_id', $requiredDocument->id)->first();

        if (! $template || ! Storage::disk('public')->exists($template->file_path)) {
            return redirect()->back()->with('status', __('Template not found.'))->with('status_type', 'error');
        }

        return Storage::disk('public')->download($template->file_path, $template->original_name);
    }

    public function destroyTemplate(RequiredDocument $requiredDocument)
    {
        $template = DocumentTemplate::where('required_document_id', $requiredDocument->id)->first();

        if ($template) {
            Storage::disk('public')->delete($template->file_path);
            $template->delete();
        }

        return redirect()->route('required-documents.index')
            ->with('status', __('Template removed.'))
            ->with('status_type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequiredDocument $requiredDocument)
    {
        $canDelete = $requiredDocument->canDelete();
        if ($canDelete !== true) {
            return redirect()
                ->route('required-documents.index')
                ->with('status', $canDelete)
                ->with('status_type', 'error');
        }

        $requiredDocument->delete();
        RequiredDocumentOrdering::normalizeSequence();
        RequiredDocument::flushCache();

        return redirect()
            ->route('required-documents.index')
            ->with('status', __('Required document deleted successfully.'))
            ->with('status_type', 'success');
    }
}
