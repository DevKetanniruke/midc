<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print labels</title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #eee; }
        .bar { padding: 10px; text-align: center; }
        .bar a, .bar button { padding: 6px 14px; margin: 0 4px; border: 1px solid #888; background: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; color: #000; font-size: 14px; display: inline-block; }
        .bar .note { color: #555; font-size: 13px; margin-top: 6px; }

        /* One A4 sheet = two half-height slots */
        .sheet { width: 190mm; height: 277mm; margin: 0 auto 8mm; background: #fff; overflow: hidden; page-break-after: always; }
        .sheet:last-of-type { page-break-after: auto; }
        .slot { height: 138.5mm; padding-bottom: 2mm; }
        .slot + .slot { border-top: 1px dashed #999; padding-top: 2mm; }

        .label { width: 100%; border: 1px solid #000; }
        .row { display: flex; border-bottom: 1px solid #000; }
        .row:last-child { border-bottom: 0; }
        .cell { flex: 1; padding: 4px 6px; }
        .cell + .cell { border-left: 1px solid #000; }
        .cell.w2 { flex: 2; }
        .cap { font-size: 8pt; line-height: 1.1; }
        .val { font-family: Georgia, serif; font-weight: bold; font-size: 12pt; text-align: center; }
        .val.big { font-size: 13pt; }
        .bc { margin-top: 2px; height: 11mm; }
        .bc svg { width: 100%; height: 100%; display: block; }
        .qr svg { width: 24mm; height: 24mm; display: block; margin: 2px auto; }
        .tiny { font-size: 9pt; font-weight: bold; }

        @media print {
            body { background: #fff; }
            .bar { display: none; }
            .sheet { margin: 0 auto; width: 100%; }
        }
    </style>
</head>
<body>
@php
    $bc = fn ($barcodes, $f) => isset($barcodes[$f]) ? new Illuminate\Support\HtmlString("<div class=\"bc\">{$barcodes[$f]}</div>") : '';
    $first = $items[0]['label'];
@endphp

<div class="bar">
    <button onclick="window.print()">Print</button>
    @if(count($items) === 1)
        <a href="{{ route('labels.print', $first) }}?copies=2">2 per page (same label)</a>
        <a href="{{ route('labels.edit', $first) }}">Edit</a>
        <a href="{{ route('labels.create', ['same' => $first->id]) }}">Same (next batch)</a>
    @endif
    <a href="{{ route('labels.index') }}">Close</a>
    <div class="note">{{ count($items) }} label{{ count($items) > 1 ? 's' : '' }} · half A4 each, two per sheet — cut along the dashed line.</div>
</div>

@foreach(collect($items)->chunk(2) as $pair)
<div class="sheet">
    @foreach($pair as $item)
        @php($label = $item['label'])
        @php($barcodes = $item['barcodes'])
        <div class="slot">
            <div class="label">
                <div class="row">
                    <div class="cell w2"><div class="cap">PART NUMBER (P)</div><div class="val big">{{ $label->part_number }}</div>{{ $bc($barcodes, 'part_number') }}</div>
                    <div class="cell qr"><div class="cap"><b>DANA QUICK RECEIVE SCAN</b></div>{!! $item['qr'] !!}</div>
                </div>
                <div class="row">
                    <div class="cell"><div class="cap">QUANTITY (Q)</div><div class="val">{{ $label->quantity }}</div>{{ $bc($barcodes, 'quantity') }}</div>
                    <div class="cell">
                        <div class="cap"><b>DESC.</b> &nbsp; <span class="val">{{ $label->part_description }}</span></div>
                        <div style="margin-top:3px"><div class="cap">DELIVERY NOTE (16K)</div><div class="val">{{ $label->delivery_note }}</div>{{ $bc($barcodes, 'delivery_note') }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell"><div class="cap">SUPPLIER (V)</div><div class="val">{{ $label->supplier_number }}</div>{{ $bc($barcodes, 'supplier_number') }}</div>
                    <div class="cell">
                        <div class="cap">HEAT LOT (ZA)<span class="val" style="float:right">{{ $label->heat_lot }}</span></div>
                        <div class="cap" style="text-align:right;margin-top:8px">DATE MFG<br><b>{{ $label->date_code }}</b></div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell"><div class="cap">VENDOR BATCH (1T)</div><div class="val">{{ $label->vendor_batch_lot }}</div>{{ $bc($barcodes, 'vendor_batch_lot') }}</div>
                    <div class="cell">
                        <div class="cap" style="text-align:right">REV. LEVEL (2P)</div><div class="val" style="text-align:right">{{ $label->revision_level }}</div>
                        <div style="width:40%;margin-left:auto">{{ $bc($barcodes, 'revision_level') }}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="cell tiny">FROM &nbsp; {{ $label->supplier_name }}<br>{{ $label->from_location }}</div>
                    <div class="cell tiny">TO &nbsp; {{ $label->dana_facility }}<br>{{ $label->to_location }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endforeach
</body>
</html>
