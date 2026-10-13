@php
    $in = 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500';
    $pre = old('mode') ? old('mode') === 'pre_register' : ($pass->exists && $pass->check_in_at === null);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('visitor-passes.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <h1 class="font-semibold text-lg text-gray-800">{{ $pass->exists ? 'Edit Visitor Pass '.$pass->pass_number : 'New Visitor Pass' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $pass->exists ? route('visitor-passes.update', $pass) : route('visitor-passes.store') }}" class="max-w-4xl mx-auto space-y-6"
          x-data="{ mode: '{{ $pre ? 'pre_register' : 'walk_in' }}' }">
        @csrf
        @if($pass->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">Please fix the highlighted fields below.</div>
        @endif

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Arrival</h2>
            <div class="p-6 grid gap-5 sm:grid-cols-2">
                @unless($pass->exists)
                    <div class="sm:col-span-2 flex flex-wrap gap-6 text-sm">
                        <label class="inline-flex items-center gap-2"><input type="radio" name="mode" value="walk_in" x-model="mode" class="text-accent-600 focus:ring-accent-500"> Walk-in — check in now</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" name="mode" value="pre_register" x-model="mode" class="text-accent-600 focus:ring-accent-500"> Pre-register — expected later</label>
                    </div>
                @endunless
                <div x-show="mode === 'walk_in'" @if($pre) x-cloak @endif>
                    <x-form-field name="check_in_at" label="In Time (Date & Time)" type="datetime-local" :required="true"
                                  :value="optional($pass->check_in_at)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')" />
                </div>
                <div x-show="mode === 'pre_register'" @unless($pre) x-cloak @endunless>
                    <x-form-field name="expected_at" label="Expected Arrival Date & Time" type="datetime-local" :required="true"
                                  :value="optional($pass->expected_at)->format('Y-m-d\TH:i') ?? now()->addHour()->startOfHour()->format('Y-m-d\TH:i')" />
                </div>
            </div>
        </section>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Visitor Details</h2>
            <div class="p-6 grid gap-5 sm:grid-cols-2">
                <x-form-field name="visitor_name" label="Visitor Name" :required="true" :value="$pass->visitor_name" />
                <x-form-field name="phone" label="Mobile No" type="tel" :required="true" :value="$pass->phone" />
                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea id="address" name="address" rows="2" maxlength="500" class="{{ $in }}{{ $errors->has('address') ? ' border-red-400' : '' }}">{{ old('address', $pass->address) }}</textarea>
                    @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-form-field name="purpose" label="Purpose of Visit" :required="true" :value="$pass->purpose" />
                <x-form-field name="person_to_meet" label="Person to Meet" :required="true" :value="$pass->person_to_meet" />
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('visitor-passes.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">
                {{ $pass->exists ? 'Update' : 'Issue' }} Visitor Pass
            </button>
        </div>
    </form>
</x-app-layout>
