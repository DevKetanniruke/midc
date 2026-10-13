@php
    $user = auth()->user();
    $in = 'rounded-md border-gray-300 text-sm shadow-sm focus:border-accent-500 focus:ring-accent-500';
    $btn = 'inline-flex items-center justify-center h-8 w-8 rounded-md border transition ';
    $ico = fn ($d) => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-800">Gate Passes</h1>
    </x-slot>

    @include('partials.bs-pagination-style')

    <div class="max-w-7xl mx-auto space-y-4">
        @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif

        <form method="GET" class="bg-white border border-gray-200 rounded-lg p-4 flex flex-wrap items-center gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search pass no., employee, department, purpose…" class="flex-1 min-w-[14rem] {{ $in }}">
            <select name="status" class="{{ $in }}">
                <option value="">All statuses</option>
                @foreach(\App\Models\GatePass::STATUSES as $k => [$t])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $t }}</option>@endforeach
            </select>
            <select name="scope" class="{{ $in }}">
                <option value="">All passes</option>
                <option value="action" @selected(request('scope') === 'action')>Awaiting my action ({{ $actionCount }})</option>
            </select>
            <button class="px-4 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">Search</button>
            @if(request()->hasAny(['q', 'status', 'scope']))<a href="{{ route('gate-passes.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:underline">Reset</a>@endif
            @can('gate-passes.create')<a href="{{ route('gate-passes.create') }}" class="ml-auto px-4 py-2 bg-accent-600 text-white rounded-md text-sm font-semibold hover:bg-accent-700">+ New Gate Pass</a>@endcan
        </form>

        @if($actionCount && request('scope') !== 'action')
            <a href="{{ route('gate-passes.index', ['scope' => 'action']) }}" class="block p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-md text-sm hover:bg-amber-100">
                <b>{{ $actionCount }}</b> gate pass{{ $actionCount > 1 ? 'es are' : ' is' }} waiting for your action →
            </a>
        @endif

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider bg-accent-50 text-accent-700 border-b-2 border-accent-200">
                        <tr>
                            <th class="px-4 py-3 w-14">Sr. No.</th><th class="px-4 py-3">Pass No.</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Employee</th><th class="px-4 py-3">Department</th>
                            <th class="px-4 py-3">Out Time</th><th class="px-4 py-3">Purpose</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($passes as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $passes->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap"><a href="{{ route('gate-passes.show', $p) }}" class="text-accent-700 hover:underline">{{ $p->pass_number }}</a></td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $p->pass_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $p->employee_name }}</td>
                            <td class="px-4 py-3">{{ $p->department }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ \App\Models\GatePass::clock($p->out_from) }} – {{ \App\Models\GatePass::clock($p->out_to) }}</td>
                            <td class="px-4 py-3">{{ $p->purpose }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold whitespace-nowrap {{ $p->statusClass() }}">{{ $p->statusLabel() }}</span></td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($p->canApprove($user))
                                        <a href="{{ route('gate-passes.show', $p) }}" title="Review &amp; approve" aria-label="Review" class="{{ $btn }} border-emerald-200 text-emerald-700 hover:bg-emerald-50">{!! $ico('M5 13l4 4L19 7') !!}</a>
                                    @endif
                                    <a href="{{ route('gate-passes.show', $p) }}" title="View" aria-label="View" class="{{ $btn }} border-sky-200 text-sky-700 hover:bg-sky-50">{!! $ico('M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 100-6 3 3 0 000 6z') !!}</a>
                                    <a href="{{ route('gate-passes.print', $p) }}" target="_blank" title="Print" aria-label="Print" class="{{ $btn }} border-violet-200 text-violet-700 hover:bg-violet-50">{!! $ico('M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a2 2 0 012-2h14a2 2 0 012 2v6a1 1 0 01-1 1h-2M6 14h12v7H6z') !!}</a>
                                    @if($p->canEdit($user))
                                        <a href="{{ route('gate-passes.edit', $p) }}" title="Edit" aria-label="Edit" class="{{ $btn }} border-amber-200 text-amber-700 hover:bg-amber-50">{!! $ico('M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4zM13.5 6.5l4 4') !!}</a>
                                    @endif
                                    @if($p->canDelete($user))
                                        <form method="POST" action="{{ route('gate-passes.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete this gate pass?')">
                                            @csrf @method('DELETE')
                                            <button title="Delete" aria-label="Delete" class="{{ $btn }} border-red-200 text-red-700 hover:bg-red-50">{!! $ico('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3') !!}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-8 text-center text-gray-500">No gate passes found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($passes->hasPages())<div class="bs-pagination px-4 py-3 border-t border-gray-200">{{ $passes->links('pagination::bootstrap-5') }}</div>@endif
        </div>
    </div>
</x-app-layout>
