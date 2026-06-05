<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estimate {{ $estimate->estimate_id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { display:flex; justify-content:space-between; align-items:center; }
        .company, .client { width:48%; }
        .title { font-size: 28px; margin-top: 20px; }
        table { width:100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border:1px solid #ddd; padding:8px; }
        th { background:#f5f5f5; }
        .summary { margin-top: 20px; width: 100%; }
        .summary .row { display:flex; justify-content:space-between; padding:6px 0; }
        .grand { font-weight:700; font-size:16px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="company">
            @if($company)
                <div><strong>{{ $company->company_name }}</strong></div>
                <div>{{ $company->address }}</div>
                <div>{{ $company->township }} Township, {{ $company->city }}, {{ $company->country }}</div>
                <div>P: {{ implode(', ', (array)$company->phone_numbers) }}</div>
                <div>E: {{ implode(', ', (array)$company->email_addresses) }}</div>
            @else
                <div><strong>{{ config('app.name') }}</strong></div>
            @endif
        </div>
        <div class="client">
            <div><strong>{{ $estimate->client_name }}</strong></div>
            @if($estimate->client)
                <div>{{ $estimate->client->address }}</div>
                <div>{{ $estimate->client->township }}, {{ $estimate->client->city }}</div>
                <div>{{ $estimate->client->country }}</div>
            @endif
        </div>
    </div>

    <div class="title">ESTIMATE: {{ $estimate->estimate_id }}</div>
    <div>Estimate Date: {{ optional($estimate->issue_date)->format('d/m/Y') ?? $estimate->issue_date }}</div>

    <table>
        <thead>
        <tr>
            <th>Item</th>
            <th>Description</th>
            <th style="text-align:center">Qty</th>
            <th style="text-align:right">Unit Price</th>
            <th style="text-align:right">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach($estimate->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->description }}</td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:right">{{ number_format($item->price,2) }}</td>
                <td style="text-align:right">{{ number_format($item->total,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="row"><span>Subtotal</span><span>{{ number_format($estimate->sub_total,2) }} {{ $estimate->currency }}</span></div>
        <div style="border-top:1px solid #eee; margin-top:8px; padding-top:8px;" class="row grand"><span>Grand Total</span><span>{{ number_format($estimate->grand_total,2) }} {{ $estimate->currency }}</span></div>
    </div>

    <div style="margin-top:24px;">{!! $estimate->terms !!}</div>
</div>
</body>
</html>
