<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\Setting;

class SettingsRepository {

    public function getOverdueHours()
    {
        $hours = Setting::where('slug', 'overdue_hours')->first();

        return $hours->value ?? 0;
    }
}