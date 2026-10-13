@use('App\Support\Permissions')
@php
    $in = 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500';
    $system = $role->exists && Permissions::isSystemRole($role->name);
    $checked = old('permissions', $granted);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <h1 class="font-semibold text-lg text-gray-800">{{ $role->exists ? 'Edit Role · '.Permissions::roleLabel($role->name) : 'New Role' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" class="max-w-4xl mx-auto space-y-6">
        @csrf
        @if($role->exists) @method('PUT') @endif

        @if($errors->any())<div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">{{ implode(' ', $errors->all()) }}</div>@endif

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Role</h2>
            <div class="p-6">
                @if($system)
                    <p class="text-sm text-gray-700"><b>{{ Permissions::roleLabel($role->name) }}</b> is a built-in role: its name is fixed, because the gate pass approval chain depends on it. You can still change its permissions.</p>
                @else
                    <x-form-field name="name" label="Role name" :required="true" :value="$role->name" hint="e.g. Store Manager." />
                @endif
            </div>
        </section>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Permissions <span class="font-normal text-sm text-gray-500">— a menu is shown only when the role can <b>View</b> (or <b>Create</b>) it</span></h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm" x-data="{ all(row, on) { row.querySelectorAll('input[type=checkbox]').forEach(c => c.checked = on) } }">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500 bg-gray-50">
                        <tr>
                            <th class="px-6 py-3">Menu</th>
                            @foreach(Permissions::ACTIONS as $label)<th class="px-4 py-3 text-center">{{ $label }}</th>@endforeach
                            <th class="px-4 py-3 text-center">All</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                    @foreach(Permissions::MODULES as $module => [$title, $actions])
                        <tr x-ref="row_{{ $loop->index }}">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $title }}</td>
                            @foreach(array_keys(Permissions::ACTIONS) as $action)
                                <td class="px-4 py-3 text-center">
                                    @if(in_array($action, $actions, true))
                                        <input type="checkbox" name="permissions[]" value="{{ $module }}.{{ $action }}" @checked(in_array("$module.$action", $checked, true))
                                               class="rounded border-gray-300 text-accent-600 focus:ring-accent-500" aria-label="{{ $title }} — {{ $action }}">
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-accent-600 focus:ring-accent-500" aria-label="All {{ $title }}"
                                       @change="all($refs.row_{{ $loop->index }}, $event.target.checked)">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="px-6 py-3 text-xs text-gray-500 border-t border-gray-200">
                Gate pass approvals (Head of Dept., HR, Security) are decided by the built-in role itself, not by a permission — those roles also need <b>Gate Passes → View</b> to see the passes they approve.
            </p>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">{{ $role->exists ? 'Update' : 'Create' }} Role</button>
        </div>
    </form>
</x-app-layout>
