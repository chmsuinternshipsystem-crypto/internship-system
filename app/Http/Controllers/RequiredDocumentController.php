<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequiredDocumentRequest;
use App\Http\Requests\UpdateRequiredDocumentRequest;
use App\Models\Company;
use App\Models\RequiredDocument;
use App\Support\RequiredDocumentOrdering;
use Illuminate\Http\Request;

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

        return redirect()
            ->to(route('required-documents.index') . ($request->input('page') ? '?page=' . (int) $request->input('page') : ''))
            ->with('status', __('Required document updated successfully.'))
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

        return redirect()
            ->route('required-documents.index')
            ->with('status', __('Required document deleted successfully.'))
            ->with('status_type', 'success');
    }
}
