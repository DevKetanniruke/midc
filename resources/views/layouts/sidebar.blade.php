@php
    $icons = [
        'home'  => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10',
        'plus'  => 'M12 5v14M5 12h14',
        'list'  => 'M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01',
        'gate'  => 'M4 21V5l8-2 8 2v16M9 21v-6h6v6',
        'users' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 11a4 4 0 100-8 4 4 0 000 8zM3 20v-2a4 4 0 014-4h4a4 4 0 014 4v2',
        'user'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0',
        'idcard' => 'M3 6h18v12H3zM7 11a1.5 1.5 0 103 0 1.5 1.5 0 00-3 0zM6.5 15c.4-1.2 1.4-2 2.5-2s2.1.8 2.5 2M14 10h4M14 13h3',
        'cog' => 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z',
        'qr' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2v2h-2zM14 18h2v2h-2zM18 18h2v2h-2z',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3zM9 12l2 2 4-4',
    ];
    $icon = fn ($n) => '<svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="'.$icons[$n].'"/></svg>';
    $item = fn ($active) => 'group relative flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition '
        .($active ? 'bg-accent-50 text-accent-700 dark:bg-accent-500/15 dark:text-accent-300'
                  : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white');
    $bar = fn ($active) => $active ? '<span class="absolute left-0 top-1.5 bottom-1.5 w-1 rounded-r bg-accent-600"></span>' : '';
    // ['item', link] is a single menu entry; ['group', title, icon, [links]] is a menu with submenus.
    // A link is [title, route, icon, active route patterns, required permission (null = always visible)].
    $nav = [
        ['item', ['Dashboard', 'dashboard', 'home', ['dashboard'], 'dashboard.view']],
        ['group', 'Barcode & QR', 'qr', [
            ['New Label', 'labels.create', 'plus', ['labels.create'], 'labels.create'],
            ['All Labels', 'labels.index', 'list', ['labels.index', 'labels.edit', 'labels.print'], 'labels.view'],
        ]],
        ['group', 'Gate Pass', 'gate', [
            ['New Gate Pass', 'gate-passes.create', 'plus', ['gate-passes.create'], 'gate-passes.create'],
            ['All Gate Passes', 'gate-passes.index', 'list', ['gate-passes.index', 'gate-passes.show', 'gate-passes.edit', 'gate-passes.print'], 'gate-passes.view'],
        ]],
        ['group', 'Visitor Pass', 'users', [
            ['New Visitor Pass', 'visitor-passes.create', 'plus', ['visitor-passes.create'], 'visitor-passes.create'],
            ['All Visitor Passes', 'visitor-passes.index', 'list', ['visitor-passes.index', 'visitor-passes.show', 'visitor-passes.edit', 'visitor-passes.print'], 'visitor-passes.view'],
        ]],
        ['group', 'Administration', 'shield', [
            ['Users', 'users.index', 'idcard', ['users.*'], 'users.view'],
            ['Roles & Permissions', 'roles.index', 'shield', ['roles.*'], 'roles.view'],
            ['Settings', 'settings.edit', 'cog', ['settings.*'], 'settings.view'],
        ]],
        ['item', ['Profile', 'profile.edit', 'user', ['profile.*'], null]],
    ];
    // Only show what the signed-in user's roles allow; a menu with no visible submenu disappears.
    $can = fn ($link) => $link[4] === null || Auth::user()->can($link[4]);
    $nav = collect($nav)->map(function ($entry) use ($can) {
        if ($entry[0] === 'item') {
            return $can($entry[1]) ? $entry : null;
        }
        $links = array_values(array_filter($entry[3], $can));

        return $links ? ['group', $entry[1], $entry[2], $links] : null;
    })->filter()->values()->all();
    $isActive = fn ($link) => $link[3] && request()->routeIs(...$link[3]);
@endphp

<aside :class="[open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0', collapsed ? 'lg:w-20' : 'lg:w-64']"
       class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col transition-all duration-200 overflow-x-hidden">
    <div :class="collapsed ? 'lg:justify-center lg:px-0' : 'px-5'" class="h-16 flex items-center justify-between px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            @if($site->logo)
                <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="h-9 w-9 shrink-0 rounded-lg object-contain">
            @else
                <span class="h-9 w-9 shrink-0 rounded-xl bg-gradient-to-br from-accent-400 to-accent-700 text-white grid place-items-center shadow">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 5v14M8 5v14M12 5v14M16 5v14M20 5v14"/></svg>
                </span>
            @endif
            <span :class="collapsed ? 'lg:hidden' : ''" class="font-bold text-lg tracking-tight text-gray-900 whitespace-nowrap">{{ $site->name }}</span>
        </a>
        <button @click="open = false" class="lg:hidden text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Close menu">&times;</button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-1">
        @foreach($nav as $entry)
            @if($entry[0] === 'item')
                @php [$title, $route, $ic] = $entry[1]; $active = $isActive($entry[1]); @endphp
                <a href="{{ route($route) }}" :class="collapsed ? 'lg:justify-center' : ''" title="{{ $title }}" class="{{ $item($active) }}">{!! $bar($active) !!}{!! $icon($ic) !!}<span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">{{ $title }}</span></a>
            @else
                @php
                    [, $groupTitle, $groupIcon, $links] = $entry;
                    $groupActive = collect($links)->contains(fn ($l) => $isActive($l));
                    $firstUrl = route($links[0][1]);
                @endphp
                {{-- Menu with submenus: opens by itself when one of its pages is showing. On the collapsed (icon-only) sidebar the icon links to the menu's first page. --}}
                <div x-data="{ g: {{ $groupActive ? 'true' : 'false' }} }">
                    <button type="button" title="{{ $groupTitle }}" aria-expanded="false" :aria-expanded="g.toString()"
                            @click="(collapsed && window.innerWidth >= 1024) ? window.location.href = '{{ $firstUrl }}' : g = !g"
                            :class="collapsed ? 'lg:justify-center' : ''"
                            class="{{ $item(false) }} w-full {{ $groupActive ? '!text-accent-700 dark:!text-accent-300' : '' }}">
                        {!! $icon($groupIcon) !!}
                        <span :class="collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">{{ $groupTitle }}</span>
                        <svg :class="[collapsed ? 'lg:hidden' : '', g ? 'rotate-90' : '']" class="ml-auto h-4 w-4 shrink-0 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div x-cloak x-show="g && !(collapsed && window.innerWidth >= 1024)" x-transition.opacity.duration.150ms
                         class="mt-1 ml-[1.35rem] pl-3 space-y-0.5 border-l border-gray-200">
                        @foreach($links as $link)
                            @php [$title, $route] = $link; $active = $isActive($link); @endphp
                            <a href="{{ route($route) }}" title="{{ $title }}" class="{{ $item($active) }} !py-1.5">{!! $bar($active) !!}<span class="whitespace-nowrap">{{ $title }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif
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
