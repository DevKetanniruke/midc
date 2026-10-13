@php
    $user = auth()->user();
    $steps = [
        ['pending_hod', 'Head of Dept.', 'hod', $pass->hodBy],
        ['pending_hr', 'HR', 'hr', $pass->hrBy],
        ['pending_security', 'Security', 'security', $pass->securityBy],
    ];
    $act = fn ($action, $text, $cls, $confirm = null) => '<form method="POST" action="'.route('gate-passes.status', $pass).'"'.($confirm ? ' onsubmit="return confirm(\''.$confirm.'\')"' : '').'>'
        .csrf_field().'<input type="hidden" name="action" value="'.$action.'"><button class="px-3 py-1.5 rounded-md border text-sm font-medium '.$cls.'">'.$text.'</button></form>';
    $clock = fn ($d) => $d?->format('d M Y, h:i A');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="font-semibold text-lg text-gray-800">Gate Pass · {{ $pass->pass_number }}</h1>
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                <a href="{{ route('gate-passes.index') }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Back</a>
                @if($pass->canEdit($user))<a href="{{ route('gate-passes.edit', $pass) }}" class="px-3 py-1.5 rounded-md border border-gray-300 hover:bg-gray-50">Edit</a>@endif
                @if($pass->canCancel($user)){!! $act('cancel', 'Cancel Pass', 'border-red-300 text-red-700 hover:bg-red-50', 'Cancel this gate pass?') !!}@endif
                <a href="{{ route('gate-passes.print', $pass) }}" target="_blank" class="px-3 py-1.5 rounded-md bg-accent-600 text-white hover:bg-accent-700">Print</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif

            {{-- Action panel for whoever the pass is currently waiting on --}}
            @if($pass->canApprove($user))
                <section class="bg-white rounded-xl border-2 border-amber-300 p-5 space-y-3" x-data="{ reject: false }">
                    <h2 class="font-semibold text-gray-900">Your approval is needed — {{ $pass->stage()[2] }}</h2>
                    <div class="flex flex-wrap gap-2">
                        {!! $act('approve', 'Approve', 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700') !!}
                        <button type="button" @click="reject = !reject" class="px-3 py-1.5 rounded-md border border-red-300 text-red-700 text-sm font-medium hover:bg-red-50">Reject…</button>
                    </div>
                    <form x-cloak x-show="reject" method="POST" action="{{ route('gate-passes.status', $pass) }}" class="space-y-2">
                        @csrf <input type="hidden" name="action" value="reject">
                        <textarea name="reason" rows="2" required maxlength="500" placeholder="Reason for rejection" class="block w-full rounded-md border-gray-300 text-sm focus:border-accent-500 focus:ring-accent-500"></textarea>
                        @error('reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        <button class="px-3 py-1.5 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">Confirm rejection</button>
                    </form>
                </section>
            @endif

            @if($pass->canRecordMovement($user))
                <section class="bg-white rounded-xl border-2 border-sky-300 p-5 space-y-3">
                    <h2 class="font-semibold text-gray-900">Security — gate movement</h2>
                    <div class="flex flex-wrap gap-2">
                        @if($pass->status === 'approved'){!! $act('out', 'Record Out Time (now)', 'border-violet-600 bg-violet-600 text-white hover:bg-violet-700') !!}@endif
                        @if($pass->status === 'out'){!! $act('in', 'Record In Time (now)', 'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700') !!}@endif
                    </div>
                </section>
            @endif

            @if($pass->status === 'rejected')
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-md text-sm">
                    Rejected by <b>{{ $pass->rejectedBy?->name ?? 'unknown' }}</b> ({{ \App\Models\GatePass::FLOW[$pass->rejected_stage][2] ?? '' }}) on {{ $clock($pass->rejected_at) }}.<br>
                    Reason: {{ $pass->reject_reason }}
                </div>
            @endif

            <section class="bg-white rounded-xl border border-gray-200">
                <h2 class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900">Details</h2>
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 p-5 text-sm">
                    @foreach([
                        'Status' => $pass->statusLabel(),
                        'Date' => $pass->pass_date->format('d M Y'),
                        'Employee' => $pass->employee_name,
                        'S/o' => $pass->father_name,
                        'Department' => $pass->department,
                        'To pass out from' => \App\Models\GatePass::clock($pass->out_from),
                        'To' => \App\Models\GatePass::clock($pass->out_to),
                        'For' => $pass->purpose,
                        'Out Time (actual)' => $clock($pass->out_time),
                        'In Time (actual)' => $clock($pass->in_time),
                        'Raised by' => $pass->creator?->name,
                    ] as $k => $v)
                        <div><dt class="text-gray-500">{{ $k }}</dt><dd class="font-medium text-gray-900">{{ $v ?: '—' }}</dd></div>
                    @endforeach
                </dl>
            </section>
        </div>

        <section class="bg-white rounded-xl border border-gray-200 p-5 self-start">
            <h2 class="font-semibold text-gray-900 mb-4">Approvals</h2>
            <ol class="space-y-4">
                @foreach($steps as [$waiting, $label, $key, $by])
                    @php
                        $at = $pass->{$key.'_at'};
                        $rejectedHere = $pass->status === 'rejected' && $pass->rejected_stage === $waiting;
                        $current = $pass->status === $waiting;
                    @endphp
                    <li class="flex gap-3">
                        <span class="mt-0.5 h-6 w-6 shrink-0 rounded-full grid place-items-center text-xs font-bold
                            {{ $at ? 'bg-emerald-100 text-emerald-700' : ($rejectedHere ? 'bg-red-100 text-red-700' : ($current ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400')) }}">
                            {{ $at ? '✓' : ($rejectedHere ? '✕' : $loop->iteration) }}
                        </span>
                        <div class="text-sm">
                            <p class="font-medium text-gray-900">{{ $label }}</p>
                            @if($at)<p class="text-gray-500">{{ $by?->name }} · {{ $clock($at) }}</p>
                            @elseif($rejectedHere)<p class="text-red-600">Rejected</p>
                            @elseif($current)<p class="text-amber-600">Waiting for approval</p>
                            @else<p class="text-gray-400">Pending</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-app-layout>
