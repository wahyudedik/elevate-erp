<?php

namespace App\Mail;

use App\Models\ManagementFinancial\FinancialReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEmailFinancialReportMail extends Mailable
{
    use Queueable, SerializesModels;

    private FinancialReport $financialReport;

    /**
     * Create a new message instance.
     */
    public function __construct(FinancialReport $financialReport)
    {
        $this->financialReport = $financialReport;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Email Financial Report Mail' . ' ' . $this->financialReport->report_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.send-email-financial-report-mail',
            with: [
                'financialReport' => $this->financialReport,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
