<?php

namespace App\Mail;

use App\Models\Support;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Mailer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket)
    {
        $this->mailTicket = $ticket;
        //
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'PMMA ICTO Helpdesk | Ticket #'.$this->mailTicket.' Submitted',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        $ticket = Support::with(['task', 'technician', 'employee.department'])->find($this->mailTicket);

        return new Content(
            view: 'mail.ticket-card',
            with: [
                'ticketNumber' => $this->mailTicket,
                'department' => optional(optional($ticket)->employee)->department->name ?? 'Not specified',
                'requestor' => optional(optional($ticket)->employee)->name ?? 'Not specified',
                'category' => optional($ticket)->task->name ?? 'Not specified',
                'details' => optional($ticket)->support_specifics ?? 'Not specified',
                'technician' => optional(optional($ticket)->technician)->name ?? 'Unassigned',
                'submittedAt' => optional(optional($ticket)->created_at)->format('m/d/Y h:iA') ?? 'Not specified',
                'status' => 'Pending',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
