@php
    $filterKeys = ['part_number', 'supplier_number', 'vendor_batch_lot', 'qty_min', 'qty_max', 'date_from', 'date_to'];
    $activeFilters = collect($filterKeys)->filter(fn ($k) => request()->filled($k))->count();
    $in = 'w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-accent-500 focus:ring-accent-500';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-800">All Labels</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4" x-data="{ filters: {{ $activeFilters ? 'true' : 'false' }}, sel: 0 }">
        @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif

        <form method="GET" id="filter-form" class="bg-white border border-gray-200 rounded-lg p-4 space-y-4">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="flex flex-wrap items-center gap-2">
                <input name="q" value="{{ request('q') }}" placeholder="Search part, batch, delivery note, description…"
                       class="flex-1 min-w-[14rem] rounded-md border-gray-300 text-sm shadow-sm focus:border-accent-500 focus:ring-accent-500">
                <button type="button" @click="filters = !filters" class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">
                    Filters @if($activeFilters)<span class="ml-1 rounded-full bg-accent-600 text-white text-xs px-1.5">{{ $activeFilters }}</span>@endif
                </button>
                <button class="px-4 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">Search</button>
                @if(request()->hasAny(['q', ...$filterKeys]))
                    <a href="{{ route('labels.index', ['per_page' => $perPage]) }}" class="px-3 py-2 text-sm text-gray-500 hover:underline">Reset</a>
                @endif
                @can('labels.create')<a href="{{ route('labels.create') }}" class="ml-auto px-4 py-2 bg-accent-600 text-white rounded-md text-sm font-semibold hover:bg-accent-700">+ New Label</a>@endcan
            </div>

            <div x-cloak x-show="filters" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 pt-4 border-t border-gray-200">
                @foreach(['part_number' => 'Part Number', 'supplier_number' => 'Supplier Number', 'vendor_batch_lot' => 'Vendor Batch Lot'] as $k => $t)
                    <div><label class="block text-xs font-medium text-gray-500 mb-1">{{ $t }}</label><input name="{{ $k }}" value="{{ request($k) }}" class="{{ $in }}"></div>
                @endforeach
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Qty min</label><input type="number" min="0" name="qty_min" value="{{ request('qty_min') }}" class="{{ $in }}"></div>
                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Qty max</label><input type="number" min="0" name="qty_max" value="{{ request('qty_max') }}" class="{{ $in }}"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Created from</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="{{ $in }}"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Created to</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="{{ $in }}"></div>
                <div class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2">
                    <a href="{{ route('labels.index', ['per_page' => $perPage]) }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Reset</a>
                    <button class="px-4 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">Apply Filters</button>
                </div>
            </div>
        </form>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-b border-gray-200 text-sm text-gray-600">
                <label class="flex items-center gap-2">Show
                    <select name="per_page" form="filter-form" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm py-1 pr-8 focus:border-accent-500 focus:ring-accent-500">
                        @foreach($perPageOptions as $n)<option value="{{ $n }}" @selected($n === $perPage)>{{ $n }}</option>@endforeach
                    </select> entries
                </label>
                <form id="bulk-print" method="GET" action="{{ route('labels.print.many') }}" target="_blank">
                    <button type="submit" x-bind:disabled="sel === 0"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-emerald-200 text-emerald-700 text-sm font-medium hover:bg-emerald-50 disabled:opacity-40 disabled:cursor-not-allowed dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a2 2 0 012-2h14a2 2 0 012 2v6a1 1 0 01-1 1h-2M6 14h12v7H6z"/></svg>
                        Print selected (<span x-text="sel">0</span>) · 2 per page
                    </button>
                </form>
                <span>
                    @if($labels->total())Showing {{ $labels->firstItem() }}–{{ $labels->lastItem() }} of {{ $labels->total() }}@else No results @endif
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider bg-accent-50 text-accent-700 border-b-2 border-accent-200 dark:bg-accent-500/15 dark:text-accent-300 dark:border-accent-500/30">
                        <tr>
                            <th class="px-4 py-3 w-10"><input type="checkbox" aria-label="Select all" @change="document.querySelectorAll('.row-check').forEach(c => c.checked = $event.target.checked); sel = document.querySelectorAll('.row-check:checked').length"></th><th class="px-4 py-3 w-14">#</th><th class="px-4 py-3">Part No.</th><th class="px-4 py-3">Qty</th><th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Supplier</th><th class="px-4 py-3">Batch</th><th class="px-4 py-3">Delivery Note</th><th class="px-4 py-3">Created</th><th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($labels as $l)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><input type="checkbox" class="row-check" name="ids[]" value="{{ $l->id }}" form="bulk-print" @change="sel = document.querySelectorAll('.row-check:checked').length"></td>
                            <td class="px-4 py-3 text-gray-500">{{ $labels->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 font-medium">{{ $l->part_number }}</td>
                            <td class="px-4 py-3">{{ $l->quantity }}</td>
                            <td class="px-4 py-3">{{ $l->part_description }}</td>
                            <td class="px-4 py-3">{{ $l->supplier_number }}</td>
                            <td class="px-4 py-3">{{ $l->vendor_batch_lot }}</td>
                            <td class="px-4 py-3">{{ $l->delivery_note }}</td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $l->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    @php
                                        $btn = 'inline-flex items-center justify-center h-8 w-8 rounded-md border transition ';
                                        $ico = fn ($d) => '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'"/></svg>';
                                    @endphp
                                    <a href="{{ route('labels.show', $l) }}" title="View" aria-label="View" class="{{ $btn }} border-sky-200 text-sky-700 hover:bg-sky-50 dark:border-sky-500/30 dark:text-sky-300 dark:hover:bg-sky-500/10">{!! $ico('M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 100-6 3 3 0 000 6z') !!}</a>
                                    <a href="{{ route('labels.print', $l) }}" title="Print" aria-label="Print" class="{{ $btn }} border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10">{!! $ico('M6 9V3h12v6M6 18H4a1 1 0 01-1-1v-6a2 2 0 012-2h14a2 2 0 012 2v6a1 1 0 01-1 1h-2M6 14h12v7H6z') !!}</a>
                                    @can('labels.edit')<a href="{{ route('labels.edit', $l) }}" title="Edit" aria-label="Edit" class="{{ $btn }} border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-500/30 dark:text-amber-300 dark:hover:bg-amber-500/10">{!! $ico('M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4zM13.5 6.5l4 4') !!}</a>@endcan
                                    @can('labels.create')<a href="{{ route('labels.create', ['same' => $l->id]) }}" title="Same (copy with next batch)" aria-label="Same" class="{{ $btn }} border-violet-200 text-violet-700 hover:bg-violet-50 dark:border-violet-500/30 dark:text-violet-300 dark:hover:bg-violet-500/10">{!! $ico('M8 8h11v11H8zM5 16V5h11') !!}</a>@endcan
@can('labels.delete')
                                    <form method="POST" action="{{ route('labels.destroy', $l) }}" class="inline" onsubmit="return confirm('Delete this label?')">
                                        @csrf @method('DELETE')
                                        <button title="Delete" aria-label="Delete" class="{{ $btn }} border-red-200 text-red-700 hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10">{!! $ico('M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3') !!}</button>
                                    </form>
@endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="p-8 text-center text-gray-500">No labels found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($labels->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">{{ $labels->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
