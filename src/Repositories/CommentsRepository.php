<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\Comment;

class CommentsRepository {

    public function getAllByTicketId($ticket_id)
    {
        return Comment::where('ticket_id', $ticket_id)->get();
    }

    public function getLastCommentByTicketId($ticket_id)
    {
        return Comment::where('ticket_id', $ticket_id)->orderBy('id', 'desc')->first();
    }
}