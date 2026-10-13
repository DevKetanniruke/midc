@php
    $in = 'rounded-md border-gray-300 text-sm shadow-sm focus:border-accent-500 focus:ring-accent-500';
    $btn = 'inline-flex items-center justify-center h-8 w-8 rounded-md border transition ';
    $ico = fn ($d) => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-800">Users</h1>
    </x-slot>

    @include('partials.bs-pagination-style')

    <div class="max-w-6xl mx-auto space-y-4">
        @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">{{ implode(' ', $errors->all()) }}</div>@endif

        <form method="GET" class="bg-white border border-gray-200 rounded-lg p-4 flex flex-wrap items-center gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search name, email, department…" class="flex-1 min-w-[14rem] {{ $in }}">
            <select name="role" class="{{ $in }}">
                <option value="">All roles</option>
                @foreach($roles as $r)<option value="{{ $r }}" @selected(request('role') === $r)>{{ \App\Support\Permissions::roleLabel($r) }}</option>@endforeach
            </select>
            <button class="px-4 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">Search</button>
            @if(request()->hasAny(['q', 'role']))<a href="{{ route('users.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:underline">Reset</a>@endif
            @can('users.create')<a href="{{ route('users.create') }}" class="ml-auto px-4 py-2 bg-accent-600 text-white rounded-md text-sm font-semibold hover:bg-accent-700">+ New User</a>@endcan
        </form>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider bg-accent-50 text-accent-700 border-b-2 border-accent-200">
                        <tr><th class="px-4 py-3 w-14">Sr. No.</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Department</th><th class="px-4 py-3">Roles</th><th class="px-4 py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($users as $u)
                        @php $locked = $u->hasRole('admin') && ! auth()->user()->hasRole('admin'); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $u->name }}@if($u->is(auth()->user()))<span class="ml-1 text-xs text-gray-400">(you)</span>@endif</td>
                            <td class="px-4 py-3">{{ $u->email }}</td>
                            <td class="px-4 py-3">{{ $u->department ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @forelse($u->roles as $r)<span class="mr-1 rounded-full px-2 py-0.5 text-xs font-semibold bg-accent-100 text-accent-700">{{ \App\Support\Permissions::roleLabel($r->name) }}</span>@empty<span class="text-gray-400">No role</span>@endforelse
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if(! $locked)
                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $u) }}" title="Edit" aria-label="Edit" class="{{ $btn }} border-amber-200 text-amber-700 hover:bg-amber-50">{!! $ico('M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4zM13.5 6.5l4 4') !!}</a>
                                        @endcan
                                        @can('users.delete')
                                            @unless($u->is(auth()->user()))
                                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline" onsubmit="return confirm('Delete this user?')">
                                                    @csrf @method('DELETE')
                                                    <button title="Delete" aria-label="Delete" class="{{ $btn }} border-red-200 text-red-700 hover:bg-red-50">{!! $ico('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3') !!}</button>
                                                </form>
                                            @endunless
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">No users found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())<div class="bs-pagination px-4 py-3 border-t border-gray-200">{{ $users->links('pagination::bootstrap-5') }}</div>@endif
        </div>
    </div>
</x-app-layout>
