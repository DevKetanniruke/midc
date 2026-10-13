@php
    $btn = 'inline-flex items-center justify-center h-8 w-8 rounded-md border transition ';
    $ico = fn ($d) => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-800">Roles &amp; Permissions</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">{{ implode(' ', $errors->all()) }}</div>@endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-600">A role decides which menus its users can see and open. Assign roles on the <a href="{{ route('users.index') }}" class="text-accent-700 hover:underline">Users</a> page.</p>
            @can('roles.create')<a href="{{ route('roles.create') }}" class="px-4 py-2 bg-accent-600 text-white rounded-md text-sm font-semibold hover:bg-accent-700">+ New Role</a>@endcan
        </div>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider bg-accent-50 text-accent-700 border-b-2 border-accent-200">
                        <tr><th class="px-4 py-3 w-14">Sr. No.</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Users</th><th class="px-4 py-3">Permissions</th><th class="px-4 py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y">
                    @foreach($roles as $r)
                        @php $system = \App\Support\Permissions::isSystemRole($r->name); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ \App\Support\Permissions::roleLabel($r->name) }}
                                @if($system)<span class="ml-1 rounded-full bg-gray-100 text-gray-600 text-[10px] font-semibold px-2 py-0.5 align-middle">Built-in</span>@endif
                            </td>
                            <td class="px-4 py-3">{{ $r->users_count }}</td>
                            <td class="px-4 py-3">{{ $r->name === 'admin' ? 'Full access' : $r->permissions_count.' of '.$total }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($r->name !== 'admin')
                                        @can('roles.edit')
                                            <a href="{{ route('roles.edit', $r) }}" title="Edit permissions" aria-label="Edit" class="{{ $btn }} border-amber-200 text-amber-700 hover:bg-amber-50">{!! $ico('M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4zM13.5 6.5l4 4') !!}</a>
                                        @endcan
                                    @endif
                                    @if(! $system)
                                        @can('roles.delete')
                                            <form method="POST" action="{{ route('roles.destroy', $r) }}" class="inline" onsubmit="return confirm('Delete this role?')">
                                                @csrf @method('DELETE')
                                                <button title="Delete" aria-label="Delete" class="{{ $btn }} border-red-200 text-red-700 hover:bg-red-50">{!! $ico('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3') !!}</button>
                                            </form>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
