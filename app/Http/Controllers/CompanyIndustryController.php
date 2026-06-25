<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyIndustryRequest;
use App\Http\Requests\UpdateCompanyIndustryRequest;
use App\Models\CompanyIndustry;

class CompanyIndustryController extends Controller
{
    public function index()
    {
        $industries = CompanyIndustry::ordered()->paginate(20);

        return view('company-industries.index', compact('industries'));
    }

    public function create()
    {
        return view('company-industries.create');
    }

    public function store(StoreCompanyIndustryRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        CompanyIndustry::create($data);

        return redirect()
            ->route('company-industries.index')
            ->with('status', __('Industry created successfully.'))
            ->with('status_type', 'success');
    }

    public function edit(CompanyIndustry $companyIndustry)
    {
        return view('company-industries.edit', compact('companyIndustry'));
    }

    public function update(UpdateCompanyIndustryRequest $request, CompanyIndustry $companyIndustry)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        $companyIndustry->update($data);

        return redirect()
            ->route('company-industries.index')
            ->with('status', __('Industry updated successfully.'))
            ->with('status_type', 'success');
    }
}
