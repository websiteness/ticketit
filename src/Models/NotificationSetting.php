<?php

namespace Kordy\Ticketit\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'event', 'category_id', 'all', 'own'];

    /**
     * @var string
     */
    protected $table = 'ticketit_notification_settings';

    public function agent()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
