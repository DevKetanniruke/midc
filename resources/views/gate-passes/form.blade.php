@php
    $in = 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500';
    $hm = fn ($t) => $t ? substr($t, 0, 5) : null;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('gate-passes.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <h1 class="font-semibold text-lg text-gray-800">{{ $pass->exists ? 'Edit Gate Pass '.$pass->pass_number : 'New Gate Pass / Out Work' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $pass->exists ? route('gate-passes.update', $pass) : route('gate-passes.store') }}" class="max-w-4xl mx-auto space-y-6">
        @csrf
        @if($pass->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">Please fix the highlighted fields below.</div>
        @endif

        <div class="rounded-md bg-sky-50 border border-sky-200 p-4 text-sm text-sky-800">
            Approval order: <b>Head of Department</b> → <b>HR</b> → <b>Security</b>. The employee may leave only after Security approves.
        </div>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Pass Details</h2>
            <div class="p-6 grid gap-5 sm:grid-cols-2">
                <x-form-field name="pass_date" label="Date" type="date" :required="true" :value="optional($pass->pass_date)->format('Y-m-d')" />
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department <span class="text-red-500">*</span></label>
                    @if($departments)
                        <select id="department" name="department" required class="{{ $in }}">
                            <option value="">— Select —</option>
                            @foreach($departments as $d)<option value="{{ $d }}" @selected(old('department', $pass->department) === $d)>{{ $d }}</option>@endforeach
                        </select>
                    @else
                        <input id="department" name="department" required value="{{ old('department', $pass->department) }}" class="{{ $in }}">
                        <p class="mt-1 text-xs text-amber-600">No Head of Department is set up yet — nobody will be able to approve this pass.</p>
                    @endif
                    @error('department')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-form-field name="employee_name" label="Please allow Mr. (Employee Name)" :required="true" :value="$pass->employee_name" />
                <x-form-field name="father_name" label="S/o Sh (Father's Name)" :value="$pass->father_name" />
                <x-form-field name="out_from" label="To pass out from" type="time" :required="true" :value="$hm($pass->out_from)" />
                <x-form-field name="out_to" label="To (return time)" type="time" :required="true" :value="$hm($pass->out_to)" />
                <x-form-field name="purpose" label="For (purpose)" :required="true" :value="$pass->purpose" class="sm:col-span-2" />
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('gate-passes.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">
                {{ $pass->exists ? 'Update' : 'Submit for Approval' }}
            </button>
        </div>
    </form>
</x-app-layout>
