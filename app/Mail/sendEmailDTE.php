<?php

namespace App\Mail;


use App\Models\SalesHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendEmailDTE extends Mailable
{
    use Queueable, SerializesModels;

    public $jsonContent;
    public $pdfContent;
    public SalesHeader $sale;
    public string $generationCode;

    /**
     * Create a new message instance.
     */
    public function __construct($jsonContent, $pdfContent, SalesHeader $sale, string $generationCode)
    {
        $this->jsonContent = $jsonContent;
        $this->pdfContent = $pdfContent;
        $this->sale = $sale;
        $this->generationCode = $generationCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('svcomputec@gmail.com', 'DTE - Documento Tributario Electrónico'),
            subject: 'DTE - Documento Tributario Electrónico ' . env('APP_NAME'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.sendDTE',
            with: ['sale' => $this->sale, 'generationCode' => $this->generationCode],
        );
    }

    /**
     * Get the attachments for the message.
     *
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->generationCode . '.pdf')
                ->withMime('application/pdf'),
            Attachment::fromData(fn () => $this->jsonContent, $this->generationCode . '.json')
                ->withMime('application/json'),
        ];
    }
}
