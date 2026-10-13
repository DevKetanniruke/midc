<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles.view', only: ['index']),
            new Middleware('permission:roles.create', only: ['create', 'store']),
            new Middleware('permission:roles.edit', only: ['edit', 'update']),
            new Middleware('permission:roles.delete', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::withCount('users', 'permissions')->orderBy('id')->get(),
            'total' => count(Permissions::all()),
        ]);
    }

    public function create(): View
    {
        return view('roles.form', ['role' => new Role(), 'granted' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(Permissions::all())],
        ]);

        Role::create(['name' => trim($data['name']), 'guard_name' => 'web'])->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('roles.form', ['role' => $role, 'granted' => $role->permissions->pluck('name')->all()]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === 'admin') {
            return redirect()->route('roles.index')->withErrors('The Administrator role always has full access and cannot be changed.');
        }

        $rules = ['permissions' => ['array'], 'permissions.*' => [Rule::in(Permissions::all())]];
        if (! Permissions::isSystemRole($role->name)) {
            $rules['name'] = ['required', 'string', 'max:50', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id)];
        }
        $data = $request->validate($rules);

        if (isset($data['name'])) {
            $role->update(['name' => trim($data['name'])]);
        }
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (Permissions::isSystemRole($role->name)) {
            return back()->withErrors('Built-in roles cannot be deleted.');
        }
        if ($role->users()->exists()) {
            return back()->withErrors('This role is assigned to users. Remove it from them first.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }
}
