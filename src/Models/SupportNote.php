<?php

namespace Kordy\Ticketit\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SupportNote extends Model
{
    protected $table = 'ticketit_support_notes';
    protected $fillable = ['notes', 'ticket_id', 'user_id'];

    /**
     * Get Ticket owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
