@php
    $in = 'rounded-md border-gray-300 text-sm shadow-sm focus:border-accent-500 focus:ring-accent-500';
    $badge = fn ($p) => $p->isStale() ? ['Not checked out', 'bg-red-100 text-red-700'] : ($p->isLate() ? ['Late', 'bg-red-100 text-red-700'] : match ($p->status) {
        'expected' => ['Expected', 'bg-sky-100 text-sky-700'],
        'out' => ['Checked Out', 'bg-emerald-100 text-emerald-700'],
        'cancelled' => ['Cancelled', 'bg-gray-100 text-gray-600'],
        default => ['Checked In', 'bg-amber-100 text-amber-700'],
    });
    $btn = 'inline-flex items-center justify-center h-8 w-8 rounded-md border transition ';
    $ico = fn ($d) => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-800">Visitor Passes</h1>
    </x-slot>

    @include('partials.bs-pagination-style')

    <div class="max-w-7xl mx-auto space-y-4">
        @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif

        <form method="GET" id="filter-form" class="bg-white border border-gray-200 rounded-lg p-4 flex flex-wrap items-center gap-2">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <input name="q" value="{{ request('q') }}" placeholder="Search pass no., visitor, mobile, address, purpose, person to meet…" class="flex-1 min-w-[14rem] {{ $in }}">
            <select name="status" class="{{ $in }}">
                <option value="">All statuses</option>
                @foreach(\App\Models\VisitorPass::STATUSES as $k => $t)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $t }}</option>@endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From" class="w-36 {{ $in }}">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To" class="w-36 {{ $in }}">
            <button class="px-4 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">Search</button>
            @if(request()->hasAny(['q', 'status', 'date_from', 'date_to']))<a href="{{ route('visitor-passes.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:underline">Reset</a>@endif
            <span class="ml-auto text-sm text-gray-600">Inside now: <b>{{ $inside }}</b> · Expected today: <b>{{ $expectedToday }}</b></span>
            @can('visitor-passes.create')<a href="{{ route('visitor-passes.create') }}" class="px-4 py-2 bg-accent-600 text-white rounded-md text-sm font-semibold hover:bg-accent-700">+ New Visitor Pass</a>@endcan
        </form>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 text-sm text-gray-600">
                <label class="flex items-center gap-2">Show
                    <select name="per_page" form="filter-form" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm py-1 pr-8 focus:border-accent-500 focus:ring-accent-500">
                        @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($n === $perPage)>{{ $n }}</option>@endforeach
                    </select> entries
                </label>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider bg-accent-50 text-accent-700 border-b-2 border-accent-200">
                        <tr>
                            <th class="px-4 py-3 w-14">#</th><th class="px-4 py-3">Pass No.</th><th class="px-4 py-3">Visitor</th>
                            <th class="px-4 py-3">Person to Meet</th><th class="px-4 py-3">Purpose</th><th class="px-4 py-3">In</th><th class="px-4 py-3">Out</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($passes as $p)
                        @php [$label, $cls] = $badge($p); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $passes->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap"><a href="{{ route('visitor-passes.show', $p) }}" class="text-accent-700 hover:underline">{{ $p->pass_number }}</a></td>
                            <td class="px-4 py-3">{{ $p->visitor_name }}</td>
                            <td class="px-4 py-3">{{ $p->person_to_meet }}</td>
                            <td class="px-4 py-3">{{ $p->purpose }}</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">@if($p->check_in_at){{ $p->check_in_at->format('d M Y H:i') }}@else<span title="Expected arrival">Exp. {{ $p->expected_at->format('d M Y H:i') }}</span>@endif</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $p->check_out_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-semibold whitespace-nowrap {{ $cls }}">{{ $label }}</span></td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
@can('visitor-passes.edit')
                                    @if($p->status === 'expected')
                                        <form method="POST" action="{{ route('visitor-passes.status', $p) }}" class="inline">
                                            @csrf <input type="hidden" name="action" value="checkin">
                                            <button title="Check in" aria-label="Check in" class="{{ $btn }} border-sky-200 text-sky-700 hover:bg-sky-50">{!! $ico('M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3') !!}</button>
                                        </form>
                                    @endif
                                    @if($p->status === 'in')
                                        <form method="POST" action="{{ route('visitor-passes.status', $p) }}" class="inline">
                                            @csrf <input type="hidden" name="action" value="checkout">
                                            <button title="Check out" aria-label="Check out" class="{{ $btn }} border-emerald-200 text-emerald-700 hover:bg-emerald-50">{!! $ico('M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9') !!}</button>
                                        </form>
                                    @endif
@endcan
                                    <a href="{{ route('visitor-passes.show', $p) }}" title="View" aria-label="View" class="{{ $btn }} border-sky-200 text-sky-700 hover:bg-sky-50">{!! $ico('M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 100-6 3 3 0 000 6z') !!}</a>
                                    <a href="{{ route('visitor-passes.print', $p) }}" target="_blank" title="Print" aria-label="Print" class="{{ $btn }} border-violet-200 text-violet-700 hover:bg-violet-50">{!! $ico('M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a2 2 0 012-2h14a2 2 0 012 2v6a1 1 0 01-1 1h-2M6 14h12v7H6z') !!}</a>
                                    @can('visitor-passes.edit')<a href="{{ route('visitor-passes.edit', $p) }}" title="Edit" aria-label="Edit" class="{{ $btn }} border-amber-200 text-amber-700 hover:bg-amber-50">{!! $ico('M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4zM13.5 6.5l4 4') !!}</a>@endcan
@can('visitor-passes.delete')
                                    <form method="POST" action="{{ route('visitor-passes.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete this visitor pass?')">
                                        @csrf @method('DELETE')
                                        <button title="Delete" aria-label="Delete" class="{{ $btn }} border-red-200 text-red-700 hover:bg-red-50">{!! $ico('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3') !!}</button>
                                    </form>
@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="p-8 text-center text-gray-500">No visitor passes found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($passes->hasPages())<div class="bs-pagination px-4 py-3 border-t border-gray-200">{{ $passes->links('pagination::bootstrap-5') }}</div>@endif
        </div>
    </div>
</x-app-layout>
