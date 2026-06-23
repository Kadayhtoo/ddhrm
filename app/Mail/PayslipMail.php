<?php

namespace App\Mail;

use App\Models\Payroll;
use Barryvdh\DomPDF\PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payroll;
    public $company;

    public function __construct(Payroll $payroll, $company)
    {
        $this->payroll = $payroll;
        $this->company = $company;
    }

    public function build()
    {
        return $this->subject('Your Payslip for ' . $this->payroll->period_start)
                    ->view('emails.payslip') 
                    ->attachData($this->generatePdf(), 'payslip_' . $this->payroll->id . '.pdf', [
                        'mime' => 'application/pdf', 
                    ]);
    }

    protected function generatePdf()
    {
       $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payslip', [
        'payroll' => $this->payroll,
        'company' => $this->company 
        ]);
    
        return $pdf->output();
    }
}