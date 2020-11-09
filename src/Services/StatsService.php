<?php
namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Repositories\TicketsRepository;
use Kordy\Ticketit\Repositories\SettingsRepository;
use Kordy\Ticketit\Repositories\CategoriesRepository;

class StatsService {

    public function getStatuses()
    {
        $tr = new TicketsRepository;
        $sr = new SettingsRepository;

        $overdue_hours = $sr->getOverdueHours();
        $datetime_now = \Carbon\Carbon::now()->subHours($overdue_hours);

        $overdue_count = $tr->getOverdueCount($datetime_now);

        $no_response_count = $tr->getNoResponseCount();

        $waiting_on_support_count = $tr->getCountByStatus(1);

        $need_feedback_count = $tr->getCountByStatus(2);

        $in_progress_count = $tr->getCountByStatus(3);

        $stats = [
            [
                'name' => 'Overdue',
                'count' => $overdue_count
            ],
            [
                'name' => 'No Response',
                'count' => $no_response_count
            ],
            [
                'name' => 'Waiting on Support',
                'count' => $waiting_on_support_count
            ],
            [
                'name' => 'Need Feedback',
                'count' => $need_feedback_count
            ],
            [
                'name' => 'In Progress',
                'count' => $in_progress_count
            ]
        ];

        return $stats;
    }

    public function getCategories()
    {
        $cr = new CategoriesRepository;

        return $cr->getCategoriesCount();
    }
}