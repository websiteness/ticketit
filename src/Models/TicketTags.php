<?php

namespace Kordy\Ticketit\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTags extends Model
{
    protected $table = 'ticketit_ticket_tags';

    protected $fillable = ['ticket_id', 'ticketit_tags_id'];

    public $timestamps = true;

    
}
