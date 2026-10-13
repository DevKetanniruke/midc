@php
    // Settings → company name, falling back to the site name.
    $company = $site->company;
    $clock = \App\Models\GatePass::clock(...);
    $signed = fn ($by, $at) => $at ? $by?->name.' · '.$at->format('d M Y h:i A') : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Gate Pass {{ $pass->pass_number }}</title>
    <style>
        @page { size: A5 landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #eee; font-size: 11pt; }
        .bar { padding: 10px; text-align: center; }
        .bar a, .bar button { padding: 6px 14px; margin: 0 4px; border: 1px solid #888; background: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; color: #000; font-size: 14px; display: inline-block; }
        .bar .note { color: #555; font-size: 13px; margin-top: 6px; }
        .sheet { width: 190mm; margin: 0 auto 8mm; background: #fff; padding: 8mm 9mm; border: 1px solid #000; }
        h1 { margin: 0; font-size: 18pt; text-align: center; }
        .sub { text-align: center; font-weight: bold; margin: 2px 0 12px; }
        .logo { display: block; margin: 0 auto 2mm; max-height: 16mm; max-width: 60mm; object-fit: contain; }
        .contact { text-align: center; font-size: 8pt; color: #444; margin: 2px 0 8px; }
        .top { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .top u { font-size: 12pt; }
        .line { display: flex; align-items: flex-end; gap: 6px; margin: 11px 0; white-space: nowrap; }
        .fill { display: inline-block; border-bottom: 1px solid #000; padding: 0 4px; min-height: 5mm; font-weight: bold; white-space: normal; flex: 1; }
        .fill.sm { flex: 0 0 38mm; }
        .stamp { font-size: 7.5pt; font-weight: normal; color: #444; display: block; }
        .no { text-align: right; font-size: 9pt; color: #444; margin-top: 4px; }
        @media print { body { background: #fff; } .bar { display: none; } .sheet { margin: 0; width: 100%; } }
    </style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('gate-passes.show', $pass) }}">Back</a>
    <div class="note">{{ $pass->pass_number }} · {{ $pass->statusLabel() }}</div>
</div>

<div class="sheet">
    @if($site->logo)<img class="logo" src="{{ $site->logo }}" alt="">@endif
    <h1>{{ $company }}</h1>
    @if($site->contact)<div class="contact">{{ $site->contact }}</div>@endif
    <div class="sub">GATE PASS / OUT WORK</div>

    <div class="top">
        <u>Time Office / Security</u>
        <span>Date : <span class="fill" style="display:inline-block;min-width:40mm;flex:none">{{ $pass->pass_date->format('d-m-Y') }}</span></span>
    </div>

    <div class="line">Please allow Mr. <span class="fill">{{ $pass->employee_name }}</span> S/o Sh <span class="fill">{{ $pass->father_name }}</span></div>
    <div class="line">Department <span class="fill">{{ $pass->department }}</span> to pass out from <span class="fill sm">{{ $clock($pass->out_from) }}</span></div>
    <div class="line">to <span class="fill sm">{{ $clock($pass->out_to) }}</span> for <span class="fill">{{ $pass->purpose }}</span></div>
    <div class="line">Sign of Employee <span class="fill"></span> Sign of Dept. Head
        <span class="fill">{{ $pass->hod_at ? 'Approved' : '' }}<span class="stamp">{{ $signed($pass->hodBy, $pass->hod_at) }}</span></span></div>
    <div class="line">Out Time <span class="fill">{{ $pass->out_time?->format('d-m-Y h:i A') }}</span> In Time <span class="fill">{{ $pass->in_time?->format('d-m-Y h:i A') }}</span></div>
    <div class="line">Sign of HR dept
        <span class="fill">{{ $pass->hr_at ? 'Approved' : '' }}<span class="stamp">{{ $signed($pass->hrBy, $pass->hr_at) }}</span></span>
        Sign of Security
        <span class="fill">{{ $pass->security_at ? 'Approved' : '' }}<span class="stamp">{{ $signed($pass->securityBy, $pass->security_at) }}</span></span></div>
    <div class="no">Pass No. {{ $pass->pass_number }}</div>
</div>
</body>
</html>
