<?php

namespace Kordy\Ticketit\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendClosedTickets extends Mailable
{
    use Queueable, SerializesModels;

    private $template;
    private $user;
    public $ticket;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $user, $ticket)
    {
        $this->template = $template;
        $this->user = $user;
        $this->ticket = $ticket;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            return $this->subject("Ticket {$this->ticket->id} closed.")
            ->replyTo($this->user->email, $this->user->name)
            ->view($this->template)
            ->with($this->user);
        } catch(\Exception $e) {
            Log::info($e->getMessage());    
        }

    }
}
