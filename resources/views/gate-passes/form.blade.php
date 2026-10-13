@php
    $items = old('items', $pass->items ?: [['description' => '', 'quantity' => 1, 'unit' => 'Nos']]);
    $type = old('type', $pass->type);
    $in = 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('gate-passes.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <h1 class="font-semibold text-lg text-gray-800">{{ $pass->exists ? 'Edit Gate Pass '.$pass->pass_number : 'New Gate Pass' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $pass->exists ? route('gate-passes.update', $pass) : route('gate-passes.store') }}"
          class="max-w-4xl mx-auto space-y-6" x-data="{ type: '{{ $type }}', items: {{ Js::from(array_values($items)) }} }">
        @csrf
        @if($pass->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">Please fix the highlighted fields below.</div>
        @endif

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Pass Details</h2>
            <div class="p-6 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Pass Type <span class="text-red-500">*</span></label>
                    <select id="type" name="type" x-model="type" class="{{ $in }}">
                        @foreach(\App\Models\GatePass::TYPES as $k => $t)<option value="{{ $k }}">{{ $t }}</option>@endforeach
                    </select>
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-form-field name="issued_at" label="Date & Time Out" type="datetime-local" :required="true"
                              :value="old('issued_at', optional($pass->issued_at)->format('Y-m-d\TH:i'))" />
                <x-form-field name="party_name" label="Party / Destination" :required="true" :value="$pass->party_name" />
                <x-form-field name="purpose" label="Purpose" :required="true" :value="$pass->purpose" hint="e.g. Repair, Sample, Delivery, Job work." />
                <div x-cloak x-show="type === 'returnable'">
                    <x-form-field name="expected_return_on" label="Expected Return Date" type="date"
                                  :value="optional($pass->expected_return_on)->format('Y-m-d')" />
                </div>
            </div>
        </section>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Vehicle & Driver</h2>
            <div class="p-6 grid gap-5 sm:grid-cols-3">
                <x-form-field name="vehicle_number" label="Vehicle Number" :value="$pass->vehicle_number" />
                <x-form-field name="driver_name" label="Driver / Carrier Name" :value="$pass->driver_name" />
                <x-form-field name="driver_phone" label="Phone" :value="$pass->driver_phone" />
            </div>
        </section>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">Items</h2>
                <button type="button" @click="items.push({ description: '', quantity: 1, unit: 'Nos' })" class="px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">+ Add item</button>
            </div>
            <div class="p-6 space-y-3">
                @error('items')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <template x-for="(item, i) in items" :key="i">
                    <div class="grid gap-2 grid-cols-12 items-start">
                        <input :name="`items[${i}][description]`" x-model="item.description" required placeholder="Description" class="col-span-12 sm:col-span-7 {{ $in }}">
                        <input :name="`items[${i}][quantity]`" x-model="item.quantity" type="number" step="any" min="0.01" required placeholder="Qty" class="col-span-5 sm:col-span-2 {{ $in }}">
                        <input :name="`items[${i}][unit]`" x-model="item.unit" placeholder="Unit" class="col-span-5 sm:col-span-2 {{ $in }}">
                        <button type="button" @click="items.length > 1 && items.splice(i, 1)" aria-label="Remove item" class="col-span-2 sm:col-span-1 h-9 rounded-md border border-red-200 text-red-700 hover:bg-red-50">&times;</button>
                    </div>
                </template>
                @foreach($errors->get('items.*') as $messages)<p class="text-sm text-red-600">{{ $messages[0] }}</p>@endforeach
            </div>
        </section>

        <section class="bg-white rounded-lg shadow-sm border border-gray-200">
            <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Remarks</h2>
            <div class="p-6">
                <textarea name="remarks" rows="3" class="{{ $in }}">{{ old('remarks', $pass->remarks) }}</textarea>
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('gate-passes.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Cancel</a>
            <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">
                {{ $pass->exists ? 'Update' : 'Generate' }} Gate Pass
            </button>
        </div>
    </form>
</x-app-layout>
