@php
    $icons = [
        'tag'   => 'M7 7h.01M3 5a2 2 0 012-2h5l9 9a2 2 0 010 2.8l-5.2 5.2a2 2 0 01-2.8 0L3 11V5z',
        'sun'   => 'M12 3v2M12 19v2M5 12H3M21 12h-2M12 8a4 4 0 100 8 4 4 0 000-8z',
        'cal'   => 'M8 3v3M16 3v3M4 9h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z',
        'box'   => 'M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8',
    ];
    $cards = [
        ['Total Labels', $stats['total'], 'tag', 'bg-accent-100 text-accent-600 dark:bg-accent-500/20 dark:text-accent-300'],
        ['Created Today', $stats['today'], 'sun', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300'],
        ['This Month', $stats['month'], 'cal', 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300'],
        ['Units Labelled', number_format($stats['units']), 'box', 'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300'],
    ];
    $max = max(1, $daily->max());
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Welcome banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-accent-600 to-accent-400 p-6 sm:p-8 text-white shadow-sm">
            <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10"></div>
            <div class="absolute right-24 -bottom-16 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-white/80">{{ now()->format('l, d F Y') }}</p>
                    <h1 class="text-2xl sm:text-3xl font-bold mt-1">Welcome back, {{ Auth::user()->name }}</h1>
                    <p class="text-white/80 mt-1">Fill the entry form and generate barcode &amp; QR labels instantly.</p>
                </div>
                <a href="{{ route('labels.create') }}" class="px-5 py-2.5 rounded-lg bg-white text-accent-700 font-semibold shadow hover:bg-accent-50">+ New Label</a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($cards as [$title, $value, $ic, $tint])
                <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4">
                    <span class="h-12 w-12 rounded-xl grid place-items-center {{ $tint }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$ic] }}"/></svg>
                    </span>
                    <div>
                        <p class="text-sm text-gray-500">{{ $title }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Labels – last 7 days</h3>
                    <span class="text-xs text-gray-500">{{ $daily->sum() }} total</span>
                </div>
                <div class="flex items-end gap-3 h-44">
                    @foreach($daily as $day => $count)
                        <div class="flex-1 flex flex-col items-center justify-end h-full gap-1.5">
                            <span class="text-xs font-medium text-gray-500">{{ $count }}</span>
                            <div class="w-full rounded-t-md bg-accent-500 hover:bg-accent-600 transition {{ $count ? '' : 'opacity-20' }}"
                                 style="height: {{ max(4, round($count / $max * 100)) }}%"></div>
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($day)->format('D') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top parts --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Top parts by quantity</h3>
                <div class="space-y-4">
                    @forelse($top as $row)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-800">{{ $row->part_number }}</span>
                                <span class="text-gray-500">{{ number_format($row->units) }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full bg-accent-500" style="width: {{ round($row->units / $top->first()->units * 100) }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Recent Labels</h3>
                <a href="{{ route('labels.index') }}" class="text-sm font-medium text-accent-600 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wider bg-accent-50 text-accent-700 border-b-2 border-accent-200 dark:bg-accent-500/15 dark:text-accent-300 dark:border-accent-500/30">
                        <tr><th class="px-5 py-2.5">Part No.</th><th class="px-5 py-2.5">Description</th><th class="px-5 py-2.5">Qty</th><th class="px-5 py-2.5">Batch</th><th class="px-5 py-2.5">Created</th><th></th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recent as $l)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $l->part_number }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $l->part_description ?: '—' }}</td>
                                <td class="px-5 py-3">{{ $l->quantity }}</td>
                                <td class="px-5 py-3"><span class="rounded-full bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300 px-2.5 py-0.5 text-xs font-medium">{{ $l->vendor_batch_lot }}</span></td>
                                <td class="px-5 py-3 text-gray-500">{{ $l->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-3 text-right"><a href="{{ route('labels.print', $l) }}" class="text-accent-600 font-medium hover:underline">Print</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No labels yet. Create your first one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
