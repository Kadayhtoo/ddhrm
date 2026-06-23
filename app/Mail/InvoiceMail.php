<?php

namespace App\Mail;

use App\Models\AboutUs;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use SerializesModels;

    public $invoice;
    public $company;

    public function __construct($invoice, $company)
    {
        $this->invoice = $invoice;
        $this->company = $company;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'company' => $this->company,
            ]
        );
    }

    public function attachments(): array
    {
        $company = AboutUs::first();

        $pdf = Pdf::loadView(
            'emails.invoice_pdf',
            [
                'invoice' => $this->invoice,
                'company' => $this->company,
            ]
        );

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                'Invoice_' . $this->invoice->invoice_id . '.pdf'
            )->withMime('invoice/pdf'),
        ];
    }

    public function build()
    {
        return $this->from('htetyinyin12556@gmail.com', 'Digital Dots Team') 
                    ->subject('Your Invoice from Digital Dots')
                    ->view('emails.invoice');
    }
}