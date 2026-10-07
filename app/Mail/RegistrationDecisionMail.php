<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $status;
    public ?string $reason;

    public function __construct(User $user, string $status, ?string $reason = null)
    {
        $this->user = $user;
        $this->status = $status;
        $this->reason = $reason;
    }

    public function build()
    {
        $subject = $this->status === 'approved'
            ? 'Account Application Approved · Cartzy'
            : 'Important: Account Application Status · Cartzy';

        return $this->subject($subject)
            ->view('emails.registration-decision', [
                'user' => $this->user,
                'status' => $this->status,
                'reason' => $this->reason,
            ]);
    }
}
