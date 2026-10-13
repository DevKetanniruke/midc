@php
    $f = fn ($name) => \App\Models\Label::FIELDS[$name];
    $sections = [
        'Part Details' => ['part_number', 'quantity', 'part_description', 'revision_level'],
        'Supplier & Order' => ['supplier_number', 'supplier_name', 'vendor_batch_lot', 'delivery_note', 'purchase_order'],
        'Traceability' => ['heat_lot', 'heat_code', 'date_code'],
        'Shipping' => ['from_location', 'dana_facility', 'to_location'],
    ];
    $hints = [
        'vendor_batch_lot' => 'Printed with data identifier 1T.',
        'date_code' => 'Manufacturing date, e.g. 10JUL2026.',
        'quantity' => 'Whole number of units in this pack.',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('labels.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back
            </a>
            <h1 class="font-semibold text-lg text-gray-800">{{ $label->exists ? 'Edit Label' : 'New Label' }}</h1>
        </div>
    </x-slot>

    <form method="POST" action="{{ $label->exists ? route('labels.update', $label) : route('labels.store') }}" class="max-w-4xl mx-auto space-y-6">
        @csrf
        @if($label->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">Please fix the highlighted fields below.</div>
        @endif

        @foreach($sections as $title => $fields)
            <section class="bg-white rounded-lg shadow-sm border border-gray-200">
                <h2 class="px-6 py-4 border-b font-semibold text-gray-800">{{ $title }}</h2>
                <div class="p-6 grid gap-5 sm:grid-cols-2">
                    @foreach($fields as $field)
                        <x-form-field :name="$field" :label="$f($field)[0]" :required="$f($field)[1]"
                                      :type="$field === 'quantity' ? 'number' : 'text'"
                                      :value="$label->{$field}" :hint="$hints[$field] ?? null"
                                      :class="in_array($field, ['part_description', 'to_location', 'from_location']) ? 'sm:col-span-2' : ''"
                                      :min="$field === 'quantity' ? 1 : null" />
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('labels.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50">Cancel</a>
            @unless($label->exists)
                <button type="reset" class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium hover:bg-gray-50"
                        onclick="this.form.querySelectorAll('input').forEach(i => { if (i.type !== 'hidden') i.value = '' }); return false;">Clear</button>
            @endunless
            <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">
                {{ $label->exists ? 'Update' : 'Generate' }} Barcode &amp; QR
            </button>
        </div>
    </form>
</x-app-layout>
