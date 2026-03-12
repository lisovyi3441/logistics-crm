<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::withCount(['users', 'orders'])->latest()->paginate(10);

        return Inertia::render('companies/Index', [
            'companies' => CompanyResource::collection($companies),
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): Response
    {
        $this->authorize('create', Company::class);

        return Inertia::render('companies/Form', [
            'company' => null,
        ]);
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): Response
    {
        $this->authorize('view', $company);

        $company->load('users.roles');
        $company->loadCount('orders');

        return Inertia::render('companies/Show', [
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): Response
    {
        $this->authorize('update', $company);

        return Inertia::render('companies/Form', [
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Update the specified company in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        if (! $company->canBeDeleted()) {
            return back()->withErrors([
                'message' => 'Cannot delete company because it has associated users or orders. Please reassign or delete them first.',
            ]);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
