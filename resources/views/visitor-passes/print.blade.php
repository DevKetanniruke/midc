<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Visitor Pass {{ $pass->pass_number }}</title>
    <style>
        @page { size: A5 landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #eee; font-size: 11pt; }
        .bar { padding: 10px; text-align: center; }
        .bar a, .bar button { padding: 6px 14px; margin: 0 4px; border: 1px solid #888; background: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; color: #000; font-size: 14px; display: inline-block; }
        .sheet { width: 190mm; margin: 0 auto 8mm; background: #fff; padding: 6mm; border: 1px solid #000; }
        h1 { margin: 0; font-size: 18pt; text-align: center; letter-spacing: 2px; }
        .sub { text-align: center; margin: 2px 0 8px; font-weight: bold; }
        .head { display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px 0; margin-bottom: 8px; }
        .head svg { width: 24mm; height: 24mm; display: block; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #000; }
        .grid div { padding: 4px 6px; border-bottom: 1px solid #000; }
        .grid div:nth-child(odd) { border-right: 1px solid #000; }
        .cap { font-size: 8pt; color: #444; }
        .sign { display: flex; justify-content: space-between; margin-top: 14mm; text-align: center; font-size: 9pt; }
        .sign span { width: 42mm; border-top: 1px solid #000; padding-top: 3px; }
        @media print { body { background: #fff; } .bar { display: none; } .sheet { margin: 0; width: 100%; } }
    </style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('visitor-passes.edit', $pass) }}">Edit</a>
    <a href="{{ route('visitor-passes.index') }}">Close</a>
</div>

<div class="sheet">
    <h1>{{ strtoupper(config('app.name')) }}</h1>
    <div class="sub">VISITOR PASS</div>

    <div class="head">
        <div>
            <div class="cap">PASS NUMBER</div><b style="font-size:16pt">{{ $pass->pass_number }}</b>
            <div class="cap" style="margin-top:6px">{{ $pass->check_in_at ? 'CHECK-IN' : 'EXPECTED ARRIVAL' }}</div><b>{{ $pass->visitAt()->format('d M Y H:i') }}</b>
        </div>
        {!! $qr !!}
    </div>

    <div class="grid">
        <div><div class="cap">VISITOR</div><b>{{ $pass->visitor_name }}</b>@if($pass->persons > 1) (+{{ $pass->persons - 1 }})@endif</div>
        <div><div class="cap">COMPANY</div><b>{{ $pass->company ?: '—' }}</b></div>
        <div><div class="cap">PHONE</div><b>{{ $pass->phone }}</b></div>
        <div><div class="cap">ID PROOF</div><b>{{ trim($pass->id_proof_type.' '.$pass->id_proof_number) ?: '—' }}</b></div>
        <div><div class="cap">PERSON TO MEET</div><b>{{ $pass->person_to_meet }}{{ $pass->department ? ' · '.$pass->department : '' }}</b></div>
        <div><div class="cap">PURPOSE</div><b>{{ $pass->purpose }}</b></div>
        <div><div class="cap">VEHICLE</div><b>{{ $pass->vehicle_number ?: '—' }}</b></div>
        <div><div class="cap">BELONGINGS</div><b>{{ $pass->belongings ?: '—' }}</b></div>
    </div>

    <div class="sign"><span>Visitor</span><span>Host signature</span><span>Security</span><span>Time out</span></div>
</div>
</body>
</html>
