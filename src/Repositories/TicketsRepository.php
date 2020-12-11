<?php
namespace Kordy\Ticketit\Repositories;

use Kordy\Ticketit\Models\Ticket;

class TicketsRepository {

    public function getById($id)
    {
        return Ticket::find($id);
    }

    public function getOverdueCount($overdue_hours)
    {
        return Ticket::where('created_at', '<', $overdue_hours)
                    ->whereNull('completed_at')
                    // ->orWhere('status_id', '!=', 4)
                    ->whereDoesntHave('comments')
                    ->count();
    }

    public function getNoResponseCount()
    {
        return Ticket::whereNull('completed_at')
                    // ->orWhere('status_id', '!=', 4)
                    ->whereDoesntHave('comments')->count();
    }

    public function getCountByStatus($status_id)
    {
        return Ticket::where('status_id', $status_id)
                    ->whereNull('completed_at')
                    // ->orWhere('status_id', '!=', 4)
                    ->has('user')
                    ->count();
    }

    public function updateById($id, $data)
    {
        return Ticket::where('id', $id)->update($data);
    }
}