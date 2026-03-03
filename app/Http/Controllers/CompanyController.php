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
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        abort_if(! auth()->user()->can(\App\Enums\Permissions::VIEW_COMPANIES->value), 403);

        $companies = Company::withCount(['users', 'orders'])->latest()->paginate(10);

        return Inertia::render('companies/Index', [
            'companies' => CompanyResource::collection($companies),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        abort_if(! auth()->user()->can(\App\Enums\Permissions::CREATE_COMPANIES->value), 403);

        return Inertia::render('companies/Form', [
            'company' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): Response
    {
        $user = auth()->user();
        abort_if(! $user->can(\App\Enums\Permissions::VIEW_COMPANIES->value) && $user->company_id !== $company->id, 403);

        $company->load('users.roles');
        $company->loadCount('orders');

        return Inertia::render('companies/Show', [
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): Response
    {
        $user = auth()->user();
        // Mangers can edit their own company, or Admins with full EDIT permission
        abort_if(! $user->can(\App\Enums\Permissions::EDIT_COMPANIES->value) && $user->company_id !== $company->id, 403);

        return Inertia::render('companies/Form', [
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        abort_if(! auth()->user()->can(\App\Enums\Permissions::DELETE_COMPANIES->value), 403);

        if ($company->users()->exists() || $company->orders()->exists()) {
            return back()->withErrors([
                'message' => 'Cannot delete company because it has associated users or orders. Please reassign or delete them first.',
            ]);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
