@php
    $icons = [
        'home'  => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10',
        'plus'  => 'M12 5v14M5 12h14',
        'list'  => 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01',
        'gate'  => 'M4 21V5l8-2 8 2v16M9 21v-6h6v6',
        'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 11a4 4 0 100-8 4 4 0 000 8zM3 20v-2a4 4 0 014-4h4a4 4 0 014 4v2',
        'user'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0',
    ];
    $icon = fn ($n) => '<svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="'.$icons[$n].'"/></svg>';
    $item = fn ($active) => 'group relative flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition '
        .($active ? 'bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white');
    $bar = fn ($active) => $active ? '<span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r bg-accent-600"></span>' : '';
    $nav = [
        ['Main', [
            ['Dashboard', 'dashboard', 'home', ['dashboard']],
        ]],
        ['Barcode & QR', [
            ['New Label', 'labels.create', 'plus', ['labels.create']],
            ['All Labels', 'labels.index', 'list', ['labels.index', 'labels.edit', 'labels.print']],
        ]],
        ['Passes', [
            ['New Gate Pass', 'gate-passes.create', 'plus', ['gate-passes.create']],
            ['Gate Passes', 'gate-passes.index', 'gate', ['gate-passes.index', 'gate-passes.show', 'gate-passes.edit', 'gate-passes.print']],
            ['New Visitor Pass', 'visitor-passes.create', 'plus', ['visitor-passes.create']],
            ['Visitor Passes', 'visitor-passes.index', 'users', ['visitor-passes.index', 'visitor-passes.show', 'visitor-passes.edit', 'visitor-passes.print']],
        ]],
        ['Account', [
            ['Profile', 'profile.edit', 'user', ['profile.*']],
        ]],
    ];
@endphp

<aside :class="[open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', collapsed ? 'lg:w-20' : 'lg:w-64']"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col transition-all duration-200 overflow-x-hidden">
    <div :class="collapsed ? 'lg:justify-center lg:px-0' : 'px-5'" class="h-16 flex items-center justify-between px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <span class="h-9 w-9 shrink-0 rounded-xl bg-gradient-to-br from-accent-400 to-accent-700 text-white grid place-items-center shadow">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 5v14M8 5v14M12 5v14M16 5v14M20 5v14"/></svg>
            </span>
            <span :class="collapsed ? 'lg:hidden' : ''" class="font-bold text-lg tracking-tight text-gray-900 whitespace-nowrap">{{ config('app.name') }}</span>
        </a>
        <button @click="open = false" class="lg:hidden text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Close menu">&times;</button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pb-4">
        @foreach($nav as [$section, $items])
            <p :class="collapsed ? 'lg:hidden' : ''" class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 whitespace-nowrap">{{ $section }}</p>
            <div :class="collapsed ? 'lg:block' : 'lg:hidden'" class="hidden my-3 border-t border-gray-200"></div>
            <div class="space-y-1">
                @foreach($items as [$title, $route, $ic, $match])
                    @if($route)
                        @php $active = $match && request()->routeIs(...$match); @endphp
                        <a href="{{ route($route) }}" :class="collapsed ? 'lg:justify-center' : ''" title="{{ $title }}" class="{{ $item($active) }}">{!! $bar($active) !!}{!! $icon($ic) !!}<span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">{{ $title }}</span></a>
                    @else
                        <span :class="collapsed ? 'lg:justify-center' : ''" title="{{ $title }}" class="{{ $item(false) }} cursor-not-allowed opacity-60">{!! $icon($ic) !!}<span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">{{ $title }}</span>
                            <em :class="collapsed ? 'lg:hidden' : ''" class="ml-auto not-italic text-[10px] font-semibold rounded-full px-2 py-0.5 bg-accent-100 text-accent-700 dark:bg-accent-500/20 dark:text-accent-300">Soon</em></span>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>

    <div class="p-3 border-t border-gray-200">
        <div :class="collapsed ? 'lg:justify-center lg:p-2' : 'p-3'" class="flex items-center gap-3 rounded-lg bg-gray-50 p-3">
            <span class="h-9 w-9 shrink-0 rounded-full bg-accent-600 text-white grid place-items-center font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
            <div :class="collapsed ? 'lg:hidden' : ''" class="min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
