<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 13px;
            margin: 25px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .header-table td {
            vertical-align: top;
        }

        .left-col {
            width: 50%;
        }

        .right-col {
            width: 50%;
            text-align: right;
        }

        .paid-status {
            color: #2e7d32;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 25px;
            text-transform: uppercase;
        }

        .customer-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .company-info {
            color: #666;
            line-height: 1.6;
            font-size: 12px;
        }

        .invoice-title {
            font-size: 34px;
            font-weight: 300;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .invoice-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.items-table th {
            background: #edf1f4;
            border: 1px solid #d9dde3;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }

        table.items-table td {
            border: 1px solid #d9dde3;
            padding: 10px;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer-section {
            margin-top: 35px;
            width: 100%;
        }

        .terms {
            width: 60%;
            float: left;
            color: #666;
            line-height: 1.8;
        }

        .summary-box {
            width: 35%;
            float: right;
            background: #edf1f4;
            padding: 15px;
            border: 1px solid #d9dde3;
        }

        .summary-row {
            margin-bottom: 10px;
        }

        .summary-label {
            float: left;
        }

        .summary-value {
            float: right;
        }

        .clear {
            clear: both;
        }

        .grand-total {
            border-top: 1px solid #ccc;
            padding-top: 10px;
            margin-top: 10px;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }

        img.logo {
            max-height: 80px;
            max-width: 180px;
        }
    </style>
</head>
<body>

<table class="header-table">
    <tr>
        <td class="left-col">
            <div class="paid-status">
                {{ strtoupper($invoice->status ?? 'UNPAID') }}
            </div>
            @if($invoice->client)
                <div class="customer-name">
                    {{ $invoice->client->name ?? $invoice->client_name }}
                </div>
                
                <div>{{ $invoice->client->address ?? '' }}</div>
                
                <div>
                    {{ $invoice->client->township->name ?? $invoice->client->township ?? '' }}
                    @if(!empty($invoice->client->city->name) || !empty($invoice->client->city))
                        , {{ $invoice->client->city->name ?? $invoice->client->city }}
                    @endif
                </div>

                <div>
                    {{ $invoice->client->country->name ?? $invoice->client->country ?? '' }}
                </div>
                
                @if(!empty($invoice->client->email))
                    <div>
                        {{ $invoice->client->email }}
                    </div>
                @endif

                @if(!empty($invoice->client->phone))
                    <div>
                        {{ $invoice->client->phone }}
                    </div>
                @endif

            @endif
        </td>

        <td class="right-col">
            @php
                $logoUrl = $company->logo_url ?? null;
                $logoPath = null;

                if ($logoUrl) {
                    $parsedUrl = parse_url($logoUrl, PHP_URL_PATH);
                    
                    $relativePath = ltrim($parsedUrl, '/');
                    
                    $fullPath = public_path($relativePath);
                    
                    if (file_exists($fullPath)) {
                        $logoPath = $fullPath;
                    }
                }
            @endphp

            @if($logoPath)
                <img src="{{ $logoPath }}" style="max-height: 80px;" alt="Logo">
            @else
                <strong>{{ $company->company_name ?? 'Our Company' }}</strong>
            @endif

            <div class="company-info">
                <strong>{{ $company->company_name ?? '' }}</strong>
                <br>{{ $company->address ?? '' }}
                <br>
                {{ $company->township ?? '' }}
                Township,
                {{ $company->city ?? '' }}

                @if(!empty($company->country))
                    <br>
                    {{ $company->country }}
                @endif

                @if(!empty($company->phone_numbers))
                    <br>
                    P:
                    {{ is_array($company->phone_numbers)
                        ? implode(', ', $company->phone_numbers)
                        : $company->phone_numbers }}
                @endif

                @if(!empty($company->email_addresses))
                    <br>
                    E:
                    {{ is_array($company->email_addresses)
                        ? implode(', ', $company->email_addresses)
                        : $company->email_addresses }}
                @endif

                @if(!empty($company->website))
                    <br>
                    W:
                    {{ $company->website }}
                @endif

            </div>
        </td>
    </tr>
</table>

<div class="invoice-title">
    INVOICE: {{ $invoice->invoice_id }}
</div>

<div class="invoice-date">
    Invoice Date:
    {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}
</div>

<table class="items-table">

    <thead>
        <tr>
            <th>Item</th>
            <th>Description</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Subtotal</th>
        </tr>
    </thead>

    <tbody>

        @foreach($invoice->items as $item)

            <tr>

                <td>
                    {{ $item->name }}
                </td>

                <td>
                    {{ $item->description }}
                </td>

                <td class="text-center">
                    {{ $item->quantity }}
                </td>

                <td class="text-right">
                    {{ number_format($item->price, 2) }}
                </td>

                <td class="text-right">
                    {{ number_format($item->quantity * $item->price, 2) }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>

<div class="footer-section">

    <div class="terms">

        {!! $invoice->terms
            ?? 'Please send payment within 7 days of receiving this invoice.' !!}

    </div>

    <div class="summary-box">

        <div class="summary-row">
            <span class="summary-label">Subtotal</span>

            <span class="summary-value">
                {{ number_format($invoice->sub_total, 2) }}
                {{ $invoice->currency }}
            </span>

            <div class="clear"></div>
        </div>

        <div class="summary-row">
            <span class="summary-label">Discount</span>

            <span class="summary-value">
                {{ number_format($invoice->discount_value, 2) }}

                @if(($invoice->discount_type ?? '') === 'percentage')
                    %
                @else
                    {{ $invoice->currency }}
                @endif
            </span>

            <div class="clear"></div>
        </div>

        <div class="grand-total">
            {{ $invoice->currency }}
            {{ number_format($invoice->grand_total, 2) }}
        </div>

    </div>

    <div class="clear"></div>

</div>

</body>
</html>