<?php
namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Models\TSetting;
use Carbon\Carbon;
use Kordy\Ticketit\Models\Status;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\User;
use Kordy\Ticketit\Mail\SendClosedTickets;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TicketsService {

    public function closeTicket()
    {
        $user = User::where('ticketit_admin', 1)->first();
        Sentinel::login($user);
        $status = TSetting::where('slug', 't_setting_ticket_status_to_check')->first();
        $num_of_days = TSetting::where('slug', 'closed_days')->first();
        $tickets = Ticket::where('status_id', $status->value)->where('updated_at', '<', now()->subDays($num_of_days->value) )->get();
        $closed_ticket_status_id = Status::where('name', 'like', '%Ticket Closed%')->first();
        foreach ($tickets as $ticket) {
            $ticket->status_id = $closed_ticket_status_id->id;
            $updated_ticket = $ticket->save();
            $this->sendMailToTicketOwners($ticket->user_id, $updated_ticket);
        }
    }


    public function sendMailToTicketOwners($user_id, $ticket)
    {
        $user = User::where('id', $user_id)->first();
        $template = 'ticketit::emails.closeticket';
        try {
            $mail = new SendClosedTickets($template, $user, $ticket);
            Mail::to($user->email)->queue($mail);
        } catch(\Exception $e) {
            Log::info($e->getMessage());    
        }
    }

    public function updateInProgressTicketsToWaitingOnSupport()
    {
        Ticket::where('created_at', '>=', Carbon::now()->subDay())->where('status_id', 3)->where('completed_at',null)->update(['status_id' => 1]);
    }
}