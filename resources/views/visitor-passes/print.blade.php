@php
    // Settings win once a company name or logo is saved; until then the original Prime Gold heading and logo are kept.
    $custom = $site->company_set || $site->logo;
    $company = $custom ? $site->company : 'Prime Gold Cables Pvt. Ltd.';
    $logo = $custom ? $site->logo : asset('images/prime-gold-logo.png');
    $date = $pass->visitAt();
    $time = fn ($d) => $d?->format('h:i A');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Visitor Gate Pass {{ $pass->pass_number }}</title>
    <style>
        @page { size: A5 portrait; margin: 8mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #eee; font-size: 11pt; }
        .bar { padding: 10px; text-align: center; }
        .bar a, .bar button { padding: 6px 14px; margin: 0 4px; border: 1px solid #888; background: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; color: #000; font-size: 14px; display: inline-block; }
        .bar .note { color: #555; font-size: 13px; margin-top: 6px; }
        .sheet { width: 130mm; margin: 0 auto 8mm; background: #fff; border: 1px solid #000; }
        .hwrap { border-bottom: 1px solid #000; padding: 3mm 3mm 2mm; }
        .head { display: flex; align-items: center; justify-content: center; gap: 5mm; }
        .head .logo img { display: block; height: 16mm; width: auto; max-width: 30mm; object-fit: contain; }
        .head h1 { flex: 0 1 auto; margin: 0; font-size: 14pt; text-align: center; text-decoration: underline; }
        .contact { text-align: center; font-size: 7.5pt; color: #444; margin-top: 2mm; }
        .title { text-align: center; font-weight: bold; font-size: 14pt; text-decoration: underline; padding: 2mm 0 3mm; }
        .body { padding: 0 3mm; }
        .f { margin: 1.4mm 0; min-height: 6mm; }
        .f b { font-weight: normal; }
        .f .v { font-weight: bold; }
        .gap { height: 5mm; }
        .center { text-align: center; font-weight: bold; margin-top: 16mm; }
        .sigs { display: flex; justify-content: space-between; padding: 14mm 3mm 3mm; font-weight: bold; font-size: 10.5pt; }
        .no { text-align: right; font-size: 8pt; color: #555; padding: 0 3mm 2mm; }
        @media print { body { background: #fff; } .bar { display: none; } .sheet { margin: 0 auto; } }
    </style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('visitor-passes.show', $pass) }}">Back</a>
    <a href="{{ route('visitor-passes.index') }}">Close</a>
    <div class="note">{{ $pass->pass_number }}</div>
</div>

<div class="sheet">
    <div class="hwrap">
        <div class="head">
            @if($logo)<div class="logo"><img src="{{ $logo }}" alt=""></div>@endif
            <h1>{{ $company }}</h1>
        </div>
        @if($site->contact)<div class="contact">{{ $site->contact }}</div>@endif
    </div>
    <div class="title">Visitor Gate Pass</div>

    <div class="body">
        <div class="f">Date :- <span class="v">{{ $date?->format('d-m-Y') }}</span></div>
        <div class="f">Visitor Name :- <span class="v">{{ $pass->visitor_name }}</span></div>
        <div class="f">Mobile No :- <span class="v">{{ $pass->phone }}</span></div>
        <div class="gap"></div>
        <div class="f" style="min-height:12mm">Address :- <span class="v">{{ $pass->address }}</span></div>
        <div class="f">Purpose of Visit :- <span class="v">{{ $pass->purpose }}</span></div>
        <div class="f">Person to meet :- <span class="v">{{ $pass->person_to_meet }}</span></div>
        <div class="f">In time :- <span class="v">{{ $time($pass->check_in_at) ?? ($pass->expected_at ? $time($pass->expected_at).' (expected)' : '') }}</span></div>
        <div class="f">Out time :- <span class="v">{{ $time($pass->check_out_at) }}</span></div>

        <div class="center">Signature of the visitor</div>
    </div>

    <div class="sigs"><span>Security officer Signature</span><span>Staff Person Signature</span></div>
    <div class="no">{{ $pass->pass_number }}</div>
</div>
</body>
</html>
