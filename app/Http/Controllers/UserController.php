<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.view', only: ['index']),
            new Middleware('permission:users.create', only: ['create', 'store']),
            new Middleware('permission:users.edit', only: ['edit', 'update']),
            new Middleware('permission:users.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $users = User::query()->with('roles')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('department', 'like', "%$s%")))
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->orderBy('id')->paginate(10)->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => Role::orderBy('id')->pluck('name')]);
    }

    public function create(Request $request): View
    {
        return view('users.form', $this->formData(new User(), $request->user()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            ...$this->rules(),
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $roles = $this->assignableRoles($request, $data['roles'] ?? []);

        $user = User::create(collect($data)->except('roles')->all() + ['email_verified_at' => now()]);
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->guardAdminTarget($request, $user);

        return view('users.form', $this->formData($user, $request->user()));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardAdminTarget($request, $user);

        $data = $request->validate([
            ...$this->rules(),
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);
        $roles = $this->assignableRoles($request, $data['roles'] ?? []);

        if ($user->hasRole('admin') && ! in_array('admin', $roles, true) && $this->adminCount() <= 1) {
            return back()->withInput()->withErrors(['roles' => 'There must be at least one Administrator.']);
        }

        $user->fill(collect($data)->except('roles', 'password')->all());
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->guardAdminTarget($request, $user);

        if ($user->is($request->user())) {
            return back()->withErrors('You cannot delete your own account here.');
        }
        if ($user->hasRole('admin') && $this->adminCount() <= 1) {
            return back()->withErrors('There must be at least one Administrator.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }

    private function formData(User $user, User $actor): array
    {
        return [
            'user' => $user,
            // Only an Administrator may hand out (or change) the Administrator role.
            'roles' => Role::orderBy('id')->pluck('name')->reject(fn ($r) => $r === 'admin' && ! $actor->hasRole('admin'))->values(),
            'current' => $user->exists ? $user->getRoleNames()->all() : [],
            'departments' => User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department'),
        ];
    }

    /** @param list<string> $roles */
    private function assignableRoles(Request $request, array $roles): array
    {
        abort_if(in_array('admin', $roles, true) && ! $request->user()->hasRole('admin'), 403, 'Only an Administrator can assign the Administrator role.');

        return $roles;
    }

    /** Non-admins may not touch Administrator accounts. */
    private function guardAdminTarget(Request $request, User $target): void
    {
        abort_if($target->hasRole('admin') && ! $request->user()->hasRole('admin'), 403, 'Only an Administrator can manage Administrator accounts.');
    }

    private function adminCount(): int
    {
        return User::role('admin')->count();
    }
}
