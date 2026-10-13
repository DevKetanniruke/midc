<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.head')
    <title>Sign in · {{ $site->name }}</title>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
<div class="min-h-screen grid lg:grid-cols-2">

    {{-- Brand panel --}}
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-accent-700 via-accent-600 to-accent-400 p-12 text-white">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10"></div>
        <div class="absolute -left-20 bottom-10 h-72 w-72 rounded-full bg-white/10"></div>

        <div class="relative flex items-center gap-3">
            @if($site->logo)
                <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="h-11 w-11 rounded-xl bg-white p-1 object-contain">
            @else
                <span class="h-11 w-11 rounded-xl bg-white/20 grid place-items-center">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 5v14M8 5v14M12 5v14M16 5v14M20 5v14"/></svg>
                </span>
            @endif
            <span class="text-xl font-bold tracking-tight">{{ $site->name }}</span>
        </div>

        <div class="relative max-w-md">
            <h2 class="text-4xl font-bold leading-tight">{{ $site->tagline ?: 'Labels, gate passes and visitor passes in one place.' }}</h2>
            <p class="mt-4 text-white/80">Generate barcode &amp; QR labels, issue gate passes with approvals, and keep a record of every visitor.</p>
        </div>

        <p class="relative text-sm text-white/70">&copy; {{ date('Y') }} {{ $site->company }}@if($site->contact) · {{ $site->contact }}@endif</p>
    </div>

    {{-- Form panel --}}
    <div class="relative flex items-center justify-center p-6 sm:p-10">
        {{-- Dark / light toggle --}}
        <button x-data="{ dark: document.documentElement.classList.contains('dark') }"
                @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('mode', dark ? 'dark' : 'light') } catch (e) {}"
                class="absolute top-4 right-4 p-2 rounded-full text-gray-500 hover:bg-gray-200 dark:hover:bg-white/10" aria-label="Toggle dark mode">
            <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 14.5A8 8 0 019.5 4 8 8 0 1020 14.5z"/></svg>
            <svg x-cloak x-show="dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        </button>

        <div class="w-full max-w-md">
            <div class="lg:hidden flex items-center gap-2.5 mb-8">
                @if($site->logo)
                    <img src="{{ $site->logo }}" alt="{{ $site->name }}" class="h-10 w-10 rounded-xl object-contain">
                @else
                    <span class="h-10 w-10 rounded-xl bg-gradient-to-br from-accent-400 to-accent-700 text-white grid place-items-center">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 5v14M8 5v14M12 5v14M16 5v14M20 5v14"/></svg>
                    </span>
                @endif
                <span class="text-lg font-bold">{{ $site->name }}</span>
            </div>

            <h1 class="text-3xl font-bold">Welcome back</h1>
            <p class="mt-1 text-gray-500">Sign in to your account to continue.</p>

            <x-auth-session-status class="mt-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" x-data="{ show: false }">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           placeholder="you@company.com"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent-500 focus:ring-accent-500 @error('email') border-red-400 @enderror">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="block w-full rounded-lg border-gray-300 shadow-sm pr-16 focus:border-accent-500 focus:ring-accent-500">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-xs font-medium text-gray-500 hover:text-gray-800" x-text="show ? 'Hide' : 'Show'">Show</button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-accent-600 shadow-sm focus:ring-accent-500">
                        <span class="ms-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-accent-600 hover:underline">Forgot password?</a>
                    @endif
                </div>

                <button class="w-full py-2.5 rounded-lg bg-accent-600 text-white font-semibold shadow-sm hover:bg-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
