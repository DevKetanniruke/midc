@php
    $act = fn ($action, $text, $cls, $confirm = null) => '<form method="POST" action="'.route('visitor-passes.status', $pass).'"'.($confirm ? ' onsubmit="return confirm(\''.$confirm.'\')"' : '').'>'
        .csrf_field().'<input type="hidden" name="action" value="'.$action.'"><button class="px-3 py-1.5 rounded-md border text-sm font-medium '.$cls.'">'.$text.'</button></form>';
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="font-semibold text-lg text-gray-800">Visitor Pass · {{ $pass->pass_number }}</h1>
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                <a href="{{ route('visitor-passes.index') }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Back</a>
                @can('visitor-passes.edit')<a href="{{ route('visitor-passes.edit', $pass) }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Edit</a>@endcan
@can('visitor-passes.edit')
                @if($pass->status === 'expected')
                    {!! $act('checkin', 'Check In', 'border-sky-300 text-sky-700 hover:bg-sky-50') !!}
                    {!! $act('cancel', 'Cancel Pass', 'border-red-300 text-red-700 hover:bg-red-50', 'Cancel this visitor pass?') !!}
                @elseif($pass->status === 'in')
                    {!! $act('checkout', 'Check Out', 'border-emerald-300 text-emerald-700 hover:bg-emerald-50') !!}
                    {!! $act('cancel', 'Cancel Pass', 'border-red-300 text-red-700 hover:bg-red-50', 'Cancel this visitor pass?') !!}
                @else
                    {!! $act('reopen', 'Reopen', 'border-gray-300 hover:bg-gray-50') !!}
                @endif
                @endcan
                <a href="{{ route('visitor-passes.print', $pass) }}" target="_blank" class="px-3 py-1.5 rounded-md bg-accent-600 text-white hover:bg-accent-700">Print</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif
            @if($pass->isLate())<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">Expected at {{ $pass->expected_at->format('d M Y H:i') }} and has not arrived yet.</div>@endif
            @if($pass->isStale())<div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">This visitor checked in on {{ $pass->check_in_at->format('d M Y') }} and has not been checked out.</div>@endif

            <section class="bg-white rounded-xl border border-gray-200">
                <h2 class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900">Details</h2>
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
                    @foreach([
                        'Status' => \App\Models\VisitorPass::STATUSES[$pass->status],
                        'Date' => $pass->visitAt()?->format('d M Y'),
                        'Visitor Name' => $pass->visitor_name,
                        'Mobile No' => $pass->phone,
                        'Purpose of Visit' => $pass->purpose,
                        'Person to Meet' => $pass->person_to_meet,
                        'Expected Arrival' => $pass->expected_at?->format('d M Y, h:i A'),
                        'In Time' => $pass->check_in_at?->format('h:i A'),
                        'Out Time' => $pass->check_out_at?->format('h:i A'),
                    ] as $k => $v)
                        <div><dt class="text-gray-500">{{ $k }}</dt><dd class="font-medium text-gray-900">{{ $v ?: '—' }}</dd></div>
                    @endforeach
                    <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900 whitespace-pre-line">{{ $pass->address ?: "—" }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="bg-white rounded-xl border border-gray-200 p-5 text-center self-start">
            <h2 class="font-semibold text-gray-900 mb-3">QR Code</h2>
            <div class="inline-block bg-white p-2 rounded [&>svg]:h-40 [&>svg]:w-40">{!! $qr !!}</div>
            <p class="mt-2 text-sm text-gray-500">{{ $pass->pass_number }}</p>
        </section>
    </div>
</x-app-layout>
