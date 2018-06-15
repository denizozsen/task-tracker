<?php

namespace App\Mail;

use App\User;
use App\Verification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invite extends Mailable
{
    use Queueable, SerializesModels;

    private $sender;

    /**
     * Create a new message instance.
     */
    public function __construct(User $sender)
    {
        $this->sender = $sender;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Task Tracker - invitation to register')
                    ->view('emails.invite')
                    ->with([
                        'sender' => $this->sender
                    ]);
    }
}
