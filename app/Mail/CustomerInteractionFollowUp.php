<?php

namespace App\Mail;

use App\Models\ManagementCRM\Customer;
use App\Models\ManagementCRM\CustomerInteraction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerInteractionFollowUp extends Mailable
{
    use Queueable, SerializesModels;

    private $customer, $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Customer $customer, $emailContent)
    {
        $this->customer = $customer;
        $this->emailContent = $emailContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Customer Interaction Follow Up' . ' ' . $this->customer->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.CustomerInteractionFollowUp',
            with: [
                'customer' => $this->customer,
                'emailContent' => $this->emailContent,
            ],
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
