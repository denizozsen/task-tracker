<?php

namespace App\Mail;

use App\User;
use App\Verification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountVerification extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $verification;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Verification $verification)
    {
        $this->user         = $user;
        $this->verification = $verification;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Task Tracker Account Verification')
                    ->view('emails.accountVerification')
                    ->with([
                        'user'             => $this->user,
                        'verificationCode' => $this->verification->code
                    ]);
    }
}
