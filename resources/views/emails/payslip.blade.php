<!DOCTYPE html>
<html>
<body>
    <h2>Hello, {{ $payroll->user->name }}</h2>
    <p>Please find your payslip for the period {{ $payroll->period_start }} to {{ $payroll->period_end }} attached.</p>
    
    <p><strong>Net Salary:</strong> {{ number_format($payroll->net_salary, 0) }} MMK</p>
    
    <br>
    <p>Best Regards,<br>
    {{ $company->name ?? 'Digital Dots' }}</p>
</body>
</html>