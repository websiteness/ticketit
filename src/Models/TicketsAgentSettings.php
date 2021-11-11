<?php

namespace Kordy\Ticketit\Models;

use Illuminate\Database\Eloquent\Model;

class TicketsAgentSettings extends Model
{
    protected $table = 'ticketit_agents_settings';

    protected $fillable = ['user_id', 'slug','data','value'];

    /**
     * Get related tickets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'status_id');
    }
}
