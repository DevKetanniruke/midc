<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Gate Pass {{ $pass->pass_number }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #eee; font-size: 11pt; }
        .bar { padding: 10px; text-align: center; }
        .bar a, .bar button { padding: 6px 14px; margin: 0 4px; border: 1px solid #888; background: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; color: #000; font-size: 14px; display: inline-block; }
        .sheet { width: 190mm; margin: 0 auto 8mm; background: #fff; padding: 8mm; border: 1px solid #000; }
        h1 { margin: 0; font-size: 20pt; text-align: center; letter-spacing: 2px; }
        .sub { text-align: center; margin: 2px 0 10px; font-weight: bold; }
        .head { display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px 0; margin-bottom: 8px; }
        .head svg { width: 24mm; height: 24mm; display: block; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; border: 1px solid #000; }
        .grid div { padding: 4px 6px; border-bottom: 1px solid #000; }
        .grid div:nth-child(odd) { border-right: 1px solid #000; }
        .cap { font-size: 8pt; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; }
        th { background: #f1f1f1; font-size: 9pt; }
        .sign { display: flex; justify-content: space-between; margin-top: 22mm; text-align: center; font-size: 9pt; }
        .sign span { width: 42mm; border-top: 1px solid #000; padding-top: 3px; }
        @media print { body { background: #fff; } .bar { display: none; } .sheet { margin: 0; width: 100%; border: 1px solid #000; } }
    </style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('gate-passes.edit', $pass) }}">Edit</a>
    <a href="{{ route('gate-passes.index') }}">Close</a>
</div>

<div class="sheet">
    <h1>{{ strtoupper(config('app.name')) }}</h1>
    <div class="sub">{{ $pass->isReturnable() ? 'RETURNABLE' : 'NON-RETURNABLE' }} GATE PASS</div>

    <div class="head">
        <div>
            <div class="cap">PASS NUMBER</div><b style="font-size:16pt">{{ $pass->pass_number }}</b>
            <div class="cap" style="margin-top:6px">DATE &amp; TIME OUT</div><b>{{ $pass->issued_at->format('d M Y H:i') }}</b>
        </div>
        {!! $qr !!}
    </div>

    <div class="grid">
        <div><div class="cap">PARTY / DESTINATION</div><b>{{ $pass->party_name }}</b></div>
        <div><div class="cap">PURPOSE</div><b>{{ $pass->purpose }}</b></div>
        <div><div class="cap">VEHICLE NUMBER</div><b>{{ $pass->vehicle_number ?: '—' }}</b></div>
        <div><div class="cap">DRIVER / CARRIER</div><b>{{ $pass->driver_name ?: '—' }}{{ $pass->driver_phone ? ' · '.$pass->driver_phone : '' }}</b></div>
        @if($pass->isReturnable())
            <div style="grid-column: span 2; border-right: 0"><div class="cap">EXPECTED RETURN DATE</div><b>{{ $pass->expected_return_on?->format('d M Y') }}</b></div>
        @endif
    </div>

    <table>
        <thead><tr><th style="width:10mm">#</th><th>Description</th><th style="width:22mm">Qty</th><th style="width:22mm">Unit</th></tr></thead>
        <tbody>
        @foreach($pass->items as $i => $item)
            <tr><td>{{ $i + 1 }}</td><td>{{ $item['description'] }}</td><td>{{ $item['quantity'] }}</td><td>{{ $item['unit'] ?? '' }}</td></tr>
        @endforeach
        </tbody>
    </table>

    @if($pass->remarks)<p><span class="cap">REMARKS</span><br>{{ $pass->remarks }}</p>@endif

    <div class="sign"><span>Prepared by</span><span>Authorised by</span><span>Security</span><span>Received by</span></div>
</div>
</body>
</html>
