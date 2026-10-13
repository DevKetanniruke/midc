@php
    $in = 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500';
    $chosen = old('roles', $current);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <h1 class="font-semibold text-lg text-gray-800">{{ $user->exists ? 'Edit User · '.$user->name : 'New User' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="max-w-3xl mx-auto space-y-6" autocomplete="off">
        @csrf
        @if($user->exists) @method('PUT') @endif

        @if($errors->any())<div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">Please fix the highlighted fields below.</div>@endif

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Account</h2>
            <div class="p-6 grid gap-5 sm:grid-cols-2">
                <x-form-field name="name" label="Name" :required="true" :value="$user->name" />
                <x-form-field name="email" label="Email (login)" type="email" :required="true" :value="$user->email" />
                <div>
                    <x-form-field name="department" label="Department" :value="$user->department" list="department-list"
                                  hint="A Head of Dept. approves gate passes only for this department." />
                    <datalist id="department-list">@foreach($departments as $d)<option value="{{ $d }}">@endforeach</datalist>
                </div>
                <div class="hidden sm:block"></div>
                <x-form-field name="password" label="{{ $user->exists ? 'New password' : 'Password' }}" type="password" :required="! $user->exists" value=""
                              :hint="$user->exists ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" autocomplete="new-password" />
                <x-form-field name="password_confirmation" label="Confirm password" type="password" :required="! $user->exists" value="" autocomplete="new-password" />
            </div>
        </section>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Roles</h2>
            <div class="p-6 space-y-3">
                @foreach($roles as $r)
                    <label class="flex items-center gap-3 text-sm">
                        <input type="checkbox" name="roles[]" value="{{ $r }}" @checked(in_array($r, $chosen, true)) class="rounded border-gray-300 text-accent-600 focus:ring-accent-500">
                        <span class="font-medium text-gray-900">{{ \App\Support\Permissions::roleLabel($r) }}</span>
                    </label>
                @endforeach
                @error('roles')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-500">The roles decide which menus this user sees. A user with no role can only open their profile.</p>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">{{ $user->exists ? 'Update' : 'Create' }} User</button>
        </div>
    </form>
</x-app-layout>
