<?php
namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Repositories\CommentsRepository;
use Kordy\Ticketit\Models\Ticket;

class TicketCommentsService {

    public function formatTags($request)
    {
        $ticket = Ticket::where('id',$request->ticket_id)->first();
        $content = $request->content;
        $content = str_replace('{{FIRST_NAME}}', $ticket->user->first_name ? $ticket->user->first_name : '' , $content);
        $content = str_replace('{{LAST_NAME}}', $ticket->user->last_name ? $ticket->user->last_name : '', $content);
        $content = str_replace('{{EMAIL}}', $ticket->user->email ? $ticket->user->email : '', $content);
        return $content;
    }


}