<?php

namespace Kordy\Ticketit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tags extends Model
{
    //use SoftDeletes;

    protected $table = 'ticketit_tags';

    protected $fillable = ['name'];

    //protected $dates = ['deleted_at'];

    /**
     * Get related tickets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->belongsToMany('Kordy\Ticketit\Models\Ticket', 'ticketit_ticket_tags', 'ticketit_tags_id', 'ticket_id');
    }
}
