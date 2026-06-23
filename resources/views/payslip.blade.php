<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip #{{ $payroll->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 10px; }
        .section { margin-bottom: 8px; }
        .two-col { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        @if($company && $company->logo_path)
            <img src="{{ public_path('storage/'. $company->logo_path) }}" style="width: 150px; margin-bottom: 10px;">
        @endif        
        <div>Payslip for {{ $payroll->period_start }} - {{ $payroll->period_end }}</div>
    </div>

    <div class="section two-col">
        <div>
            <strong>Employee</strong><br>
            {{ $payroll->user->name }}<br>
            ID: {{ $payroll->user->id }}<br>
            Department: {{ $payroll->user->department?->name ?? '-' }}
        </div>
        <div>
            <strong>Payroll</strong><br>
            Payslip #: {{ $payroll->id }}<br>
            Status: {{ $payroll->status }}<br>
            Calculated at: {{ $payroll->calculated_at }}
        </div>
    </div>

    <div class="section">
        <table>
            <tr>
                <th>Earnings</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Base salary</td>
                <td style="text-align: right;">{{ number_format($payroll->base_salary, 2) }}</td>
            </tr>
            <tr>
                <th>Deductions</th>
                <th>Amount</th>
            </tr>
            <tr>
                <td>Late penalty</td>
                <td style="text-align: right;">{{ number_format($payroll->late_penalty, 2) }}</td>
            </tr>
            <tr>
                <td>Unpaid leave deduction</td>
                <td style="text-align: right;">{{ number_format($payroll->unpaid_leave_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Paid leave deduction</td>
                <td style="text-align: right;">{{ number_format($payroll->paid_leave_deduction, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total deductions</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($payroll->total_deductions, 2) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Net pay</strong></td>
                <td style="text-align: right;"><strong>{{ number_format($payroll->net_salary, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <strong>Notes</strong><br>
        {{ $payroll->note ?? '-' }}
    </div>

    <div class="section" style="margin-top: 20px;">
        <table>
            <tr>
                <td>Prepared by</td>
                <td>Approved by</td>
            </tr>
            <tr style="height: 60px;">
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>
</body>
</html>
