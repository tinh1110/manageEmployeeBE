<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NewAttendance extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Attendance',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new_attendance',
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

    public function build()
    {
        return $this
            ->view('emails.new_attendance')
            ->with([
                'name' => $this->data['name'],
                'email' => $this->data['email'],
                'type_name' => $this->data['type_name'],
                'reason' => $this->data['reason'] ?? null,
                'start_date' => $this->data['start_date'],
                'end_date' => $this->data['end_date'],
                'start_time' => $this->data['start_time'],
                'end_time' => $this->data['end_time'],
            ])
            ->markdown('emails.new_attendance');
    }
}
