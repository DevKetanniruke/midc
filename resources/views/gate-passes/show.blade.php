@php
    $act = fn ($action, $text, $cls, $confirm = null) => '<form method="POST" action="'.route('gate-passes.status', $pass).'"'.($confirm ? ' onsubmit="return confirm(\''.$confirm.'\')"' : '').'>'
        .csrf_field().'<input type="hidden" name="action" value="'.$action.'"><button class="px-3 py-1.5 rounded-md border text-sm font-medium '.$cls.'">'.$text.'</button></form>';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="font-semibold text-lg text-gray-800">Gate Pass · {{ $pass->pass_number }}</h1>
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                <a href="{{ route('gate-passes.index') }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Back</a>
                <a href="{{ route('gate-passes.edit', $pass) }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Edit</a>
                @if($pass->status === 'open')
                    @if($pass->isReturnable()){!! $act('return', 'Mark Returned', 'border-emerald-300 text-emerald-700 hover:bg-emerald-50') !!}@endif
                    {!! $act('cancel', 'Cancel Pass', 'border-red-300 text-red-700 hover:bg-red-50', 'Cancel this gate pass?') !!}
                @else
                    {!! $act('reopen', 'Reopen', 'border-gray-300 hover:bg-gray-50') !!}
                @endif
                <a href="{{ route('gate-passes.print', $pass) }}" target="_blank" class="px-3 py-1.5 rounded-md bg-accent-600 text-white hover:bg-accent-700">Print</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif
            @if($pass->isOverdue())<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">Overdue — expected back on {{ $pass->expected_return_on->format('d M Y') }}.</div>@endif

            <section class="bg-white rounded-xl border border-gray-200">
                <h2 class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900">Details</h2>
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
                    @foreach([
                        'Type' => \App\Models\GatePass::TYPES[$pass->type],
                        'Status' => \App\Models\GatePass::STATUSES[$pass->status],
                        'Party / Destination' => $pass->party_name,
                        'Purpose' => $pass->purpose,
                        'Date & Time Out' => $pass->issued_at->format('d M Y H:i'),
                        'Expected Return' => $pass->expected_return_on?->format('d M Y'),
                        'Returned At' => $pass->returned_at?->format('d M Y H:i'),
                        'Vehicle Number' => $pass->vehicle_number,
                        'Driver / Carrier' => $pass->driver_name,
                        'Phone' => $pass->driver_phone,
                    ] as $k => $v)
                        <div><dt class="text-gray-500">{{ $k }}</dt><dd class="font-medium text-gray-900">{{ $v ?: '—' }}</dd></div>
                    @endforeach
                    <div class="sm:col-span-2"><dt class="text-gray-500">Remarks</dt><dd class="font-medium text-gray-900 whitespace-pre-line">{{ $pass->remarks ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <h2 class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900">Items</h2>
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500 bg-gray-50"><tr><th class="px-5 py-2 w-12">#</th><th class="px-5 py-2">Description</th><th class="px-5 py-2">Qty</th><th class="px-5 py-2">Unit</th></tr></thead>
                    <tbody class="divide-y">
                    @foreach($pass->items as $i => $item)
                        <tr><td class="px-5 py-2 text-gray-500">{{ $i + 1 }}</td><td class="px-5 py-2">{{ $item['description'] }}</td><td class="px-5 py-2">{{ $item['quantity'] }}</td><td class="px-5 py-2">{{ $item['unit'] ?? '' }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
        </div>

        <section class="bg-white rounded-xl border border-gray-200 p-5 text-center self-start">
            <h2 class="font-semibold text-gray-900 mb-3">QR Code</h2>
            <div class="inline-block bg-white p-2 rounded [&>svg]:h-40 [&>svg]:w-40">{!! $qr !!}</div>
            <p class="mt-2 text-sm text-gray-500">{{ $pass->pass_number }}</p>
        </section>
    </div>
</x-app-layout>
