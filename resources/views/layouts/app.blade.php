<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.head')
        <title>{{ $site->name }}</title>
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div x-data="{
                open: false,
                collapsed: (() => { try { return localStorage.getItem('sidebar') === 'collapsed' } catch (e) { return false } })(),
                toggleSidebar() { this.collapsed = !this.collapsed; try { localStorage.setItem('sidebar', this.collapsed ? 'collapsed' : 'open') } catch (e) {} }
             }" class="min-h-screen">
            <div x-cloak x-show="open" @click="open = false" class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"></div>

            @include('layouts.sidebar')

            <div :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'" class="lg:pl-64 flex flex-col min-h-screen transition-[padding] duration-200">
                {{-- Top bar --}}
                <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-gray-200 h-16 flex items-center gap-3 px-4 sm:px-6">
                    <button @click="open = true" class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100" aria-label="Open menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <button @click="toggleSidebar()" class="hidden lg:block p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100"
                            :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'" aria-label="Toggle sidebar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/>
                            <path x-show="!collapsed" stroke-linecap="round" stroke-linejoin="round" d="M16 10l-2 2 2 2"/>
                            <path x-cloak x-show="collapsed" stroke-linecap="round" stroke-linejoin="round" d="M14 10l2 2-2 2"/>
                        </svg>
                    </button>

                    <form action="{{ route('labels.index') }}" class="hidden sm:block relative w-full max-w-sm">
                        <svg class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/></svg>
                        <input name="q" value="{{ request('q') }}" placeholder="Search labels…" class="w-full pl-9 rounded-full bg-gray-50 border-gray-200 text-sm focus:border-accent-500 focus:ring-accent-500">
                    </form>

                    <div class="ml-auto flex items-center gap-1 sm:gap-2"
                         x-data="{
                            dark: document.documentElement.classList.contains('dark'),
                            accent: document.documentElement.dataset.accent,
                            pick: false,
                            toggle() { this.dark = !this.dark; document.documentElement.classList.toggle('dark', this.dark); try { localStorage.setItem('mode', this.dark ? 'dark' : 'light') } catch (e) {} },
                            setAccent(a) { this.accent = a; document.documentElement.dataset.accent = a; this.pick = false; try { localStorage.setItem('accent', a) } catch (e) {} }
                         }">
                        {{-- Dark / light --}}
                        <button @click="toggle()" class="p-2 rounded-full text-gray-500 hover:bg-gray-100" :title="dark ? 'Switch to light' : 'Switch to dark'" aria-label="Toggle dark mode">
                            <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 14.5A8 8 0 019.5 4 8 8 0 1020 14.5z"/></svg>
                            <svg x-cloak x-show="dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                        </button>

                        {{-- Accent colour --}}
                        <div class="relative" @click.outside="pick = false">
                            <button @click="pick = !pick" class="p-2 rounded-full hover:bg-gray-100" title="Colour theme" aria-label="Colour theme">
                                <span class="block h-5 w-5 rounded-full bg-accent-500 ring-2 ring-offset-2 ring-accent-200 ring-offset-white dark:ring-offset-gray-900"></span>
                            </button>
                            <div x-cloak x-show="pick" x-transition class="absolute right-0 mt-2 w-48 rounded-lg bg-white border border-gray-200 shadow-lg p-3 z-50">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Colour theme</p>
                                <div class="grid grid-cols-6 gap-2">
                                    @foreach(['indigo' => '#6366f1', 'emerald' => '#10b981', 'rose' => '#f43f5e', 'amber' => '#f59e0b', 'sky' => '#0ea5e9', 'violet' => '#8b5cf6'] as $name => $hex)
                                        <button @click="setAccent('{{ $name }}')" title="{{ ucfirst($name) }}" style="background: {{ $hex }}"
                                                class="h-6 w-6 rounded-full ring-2 ring-offset-2 ring-offset-white dark:ring-offset-gray-900"
                                                :class="accent === '{{ $name }}' ? 'ring-gray-400' : 'ring-transparent'"></button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- User --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 pl-2 text-sm font-medium text-gray-700">
                                    <span class="h-8 w-8 rounded-full bg-accent-600 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    <span class="hidden md:block">{{ Auth::user()->name }}</span>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6">
                    @isset($header)
                        <div class="max-w-7xl mx-auto mb-5">{{ $header }}</div>
                    @endisset
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
