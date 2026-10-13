<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="font-semibold text-lg text-gray-800">Label · {{ $label->part_number }}</h1>
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                <a href="{{ route('labels.index') }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Back</a>
                @can('labels.edit')<a href="{{ route('labels.edit', $label) }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Edit</a>@endcan
                @can('labels.create')<a href="{{ route('labels.create', ['same' => $label->id]) }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Same</a>@endcan
                <a href="{{ route('labels.print', $label) }}" class="px-3 py-1.5 rounded-md bg-accent-600 text-white hover:bg-accent-700">Print</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2 bg-white rounded-xl border border-gray-200">
            <h2 class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900">Details</h2>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
                @foreach(\App\Models\Label::FIELDS as $field => [$title, , $id])
                    <div class="{{ $field === 'part_description' ? 'sm:col-span-2' : '' }}">
                        <dt class="text-gray-500">{{ $title }} @if($id)<span class="text-xs text-gray-400">({{ $id }})</span>@endif</dt>
                        <dd class="font-medium text-gray-900">{{ $label->{$field} ?: '—' }}</dd>
                    </div>
                @endforeach
                <div><dt class="text-gray-500">Created</dt><dd class="font-medium text-gray-900">{{ $label->created_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </section>

        <div class="space-y-6">
            <section class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <h2 class="font-semibold text-gray-900 mb-3">QR Code</h2>
                <div class="inline-block bg-white p-2 rounded [&>svg]:h-40 [&>svg]:w-40">{!! $qr !!}</div>
            </section>

            <section class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <h2 class="font-semibold text-gray-900">Barcodes</h2>
                @forelse($barcodes as $field => $svg)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">{{ \App\Models\Label::FIELDS[$field][0] }}</p>
                        <div class="bg-white p-2 rounded h-16 [&>svg]:h-full [&>svg]:w-full">{!! $svg !!}</div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No barcode data.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
