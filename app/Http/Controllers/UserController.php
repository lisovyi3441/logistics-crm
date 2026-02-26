<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $users = User::with(['company', 'roles'])->latest()->paginate(10);

        return Inertia::render('users/Index', [
            'users' => UserResource::collection($users),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('users/Form', [
            'user' => null,
            'companies' => Company::select('id', 'name')->orderBy('name')->get(),
            'roles' => Role::select('id', 'name')->orderBy('name')->get(),
            'default_company_id' => request()->query('company_id'),
            'redirect_to' => request()->query('redirect_to'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
        ]);

        $user->assignRole($validated['role']);

        if (request()->has('redirect_to') && str_starts_with(request('redirect_to'), '/')) {
            return redirect(request('redirect_to'))->with('success', 'User created successfully.');
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     * We don't have a specific user show page yet, usually handled by editing.
     */
    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->withErrors(['message' => 'You cannot edit your own role/company here. Use the Profile settings.']);
        }

        $user->load(['company', 'roles']);

        return Inertia::render('users/Form', [
            'user' => new UserResource($user),
            'companies' => Company::select('id', 'name')->orderBy('name')->get(),
            'roles' => Role::select('id', 'name')->orderBy('name')->get(),
            'redirect_to' => request()->query('redirect_to'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->withErrors(['message' => 'You cannot edit your own profile here.']);
        }

        $validated = $request->validated();

        $updateData = Arr::only($validated, ['name', 'email', 'company_id']);

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sync role (removes old, attaches new)
        $user->syncRoles([$validated['role']]);

        if (request()->has('redirect_to') && str_starts_with(request('redirect_to'), '/')) {
            return redirect(request('redirect_to'))->with('success', 'User updated successfully.');
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['message' => 'You cannot delete yourself.']);
        }

        if (\App\Models\Order::where('user_id', $user->id)->exists()) {
            return back()->withErrors([
                'message' => 'Cannot delete user because they have associated orders. Please reassign or delete the orders first.',
            ]);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
