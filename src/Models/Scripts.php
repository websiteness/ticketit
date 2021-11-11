<?php

namespace Kordy\Ticketit\Models;

use Illuminate\Database\Eloquent\Model;

class Scripts extends Model
{
    protected $table = 'ticketit_scripts';

    protected $fillable = ['title','content'];

}
