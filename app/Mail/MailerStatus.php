<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MailerStatus extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($_ticketNumber, $_status, $_dateRequest, $_name, $_department, $_task, $_specifics, $_findings, $_technician, $_dateAccomplished)
    {
        //$this->ticket = $ticket;
        //$this->status = $status;
        //
        $this->ticketNumber = $_ticketNumber;
        $this->status = $_status;
        $this->dateRequest = $_dateRequest;
        $this->name = $_name;
        $this->department = $_department;
        $this->task = $_task;
        $this->specifics = $_specifics;
        $this->findings = $_findings;
        $this->technician = $_technician;
        $this->dateAccomplished = $_dateAccomplished;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'ITSU - JOB COMPLETION REPORT',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        // Generate a signed URL with the ticket number
        $signedUrl = URL::signedRoute('take-survey', ['taskId' => $this->ticketNumber]);

        return new Content(
            markdown: 'mail.mailer-status',
            with: [
                //'ticket' => $this->ticket,
                //'status' => $this->status,
                'ticketNumber' => $this->ticketNumber,
                'status' => $this->status,
                'dateRequest' => $this->dateRequest,
                'name' => $this->name,
                'department' => $this->department,
                'task' => $this->task,
                'specifics' => $this->specifics,
                'findings' => $this->findings,
                'technician' => $this->technician,
                'dateAccomplished' => $this->dateAccomplished,
                'surveyUrl' => $signedUrl, // <-- Pass signed URL to the Blade template
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
