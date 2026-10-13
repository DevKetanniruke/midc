<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label scan · {{ $site->name }}</title>
    <link rel="icon" href="{{ $site->favicon }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; }
        .card { max-width: 440px; margin: 24px auto; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; }
        h1 { margin: 0; padding: 16px 20px; font-size: 18px; background: #4f46e5; color: #fff; }
        dl { margin: 0; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; }
        dt { color: #6b7280; font-size: 14px; }
        dd { margin: 0; font-weight: 700; font-size: 16px; text-align: right; word-break: break-all; }
        .raw { padding: 14px 20px; font-size: 13px; color: #4b5563; word-break: break-all; background: #f9fafb; }
        .raw b { display: block; color: #111827; font-size: 15px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="card">
    <h1>@if($site->logo)<img src="{{ $site->logo }}" alt="" style="height:26px;vertical-align:middle;margin-right:8px;background:#fff;border-radius:4px;padding:2px">@endif{{ $site->name }} · Label</h1>
    <dl>
        @foreach(['Part No.', 'Vendor Batch', 'Quantity', 'Heat Lot', 'Revision Level'] as $i => $title)
            <div class="row"><dt>{{ $title }}</dt><dd>{{ $values[$i] !== '' ? $values[$i] : '—' }}</dd></div>
        @endforeach
    </dl>
    <div class="raw">Scan data<b>{{ $raw }}</b></div>
</div>
</body>
</html>
