<?php 
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class EstimateMail extends Mailable
{
    use SerializesModels;
    public $estimate;
    public $company;

    public function __construct($estimate, $company)
    {
        $this->estimate = $estimate;
        $this->company = $company;
    }

    public function content(): Content
    {
        return new Content(view: 'emails.estimate'); 
    }

    public function attachments(): array
    {
        $pdf = Pdf::loadView('emails.estimate_pdf', [
            'estimate' => $this->estimate,
            'company' => $this->company
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Estimate_' . $this->estimate->estimate_id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    public function build()
    {
        return $this->from('htetyinyin12556@gmail.com', 'Digital Dots Team') 
                    ->subject('Your Estimate from Digital Dots')
                    ->view('emails.estimate');
    }
}