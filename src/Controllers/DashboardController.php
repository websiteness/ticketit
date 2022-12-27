<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Models\Category;
use Kordy\Ticketit\Models\Ticket;
use Sentinel;
use App\User;
use Kordy\Ticketit\Services\TicketsService;

class DashboardController extends Controller
{
    public function index($indicator_period = 2)
    {
 
        $tickets_count = Ticket::count();
   

        // Per Category pagination
        $categories = Category::paginate(10, ['*'], 'cat_page');

        // Total tickets counter per category for google pie chart
        $categories_all = Category::all();
        $categories_share = [];
        foreach ($categories_all as $cat) {
            $categories_share[$cat->name] = $cat->tickets()->count();
        }

        // Total tickets counter per agent for google pie chart
        $agents_share_obj = Agent::agents()->with(['agentTotalTickets' => function ($query) {
            $query->addSelect(['id', 'agent_id']);
        }])->get();

        $agents_share = [];
        foreach ($agents_share_obj as $agent_share) {
            $agents_share[$agent_share->name] = $agent_share->agentTotalTickets->count();
        }

        // Per Agent
        $agents = Agent::agents(10);

        // Per User
        $users = Agent::users(10);

        // Per Category performance data
        $ticketController = new TicketsController(new Ticket(), new Agent(), new User());
        $monthly_performance = $ticketController->monthlyPerfomance($indicator_period);

        if (request()->has('cat_page')) {
            $active_tab = 'cat';
        } elseif (request()->has('agents_page')) {
            $active_tab = 'agents';
        } elseif (request()->has('users_page')) {
            $active_tab = 'users';
        } else {
            $active_tab = 'cat';
        }
             
        $ticketService = new TicketsService();
        $open_tickets_count = $ticketService->getOpenTicketCount();
        $no_response_tickets_count = $ticketService->getNoResponseTicketCount();;
        $in_progress_tickets_count = $ticketService->getInProgressTicketCount();
        $waiting_feedback_tickets_count = $ticketService->getWaitingOnFeedbackCount();
        $has_response_tickets_count = $ticketService->getTicketsWhereUserHasResponse();
        $closed_tickets_count = $tickets_count - $open_tickets_count;
        $ticket_average_time_total = $ticketService->getTotalAverageResponse();
        $ticket_average_time_thirty = $ticketService->getTotalAverageResponseThirtyDays();
        $ticket_average_time_seven = $ticketService->getTotalAverageResponseSevenDays();
          

        return view(
            'ticketit::admin.index',
            compact(
                'open_tickets_count',
                'closed_tickets_count',
                'tickets_count',
                'categories',
                'agents',
                'users',
                'monthly_performance',
                'categories_share',
                'agents_share',
                'active_tab',
                'no_response_tickets_count',
                'in_progress_tickets_count',
                'waiting_feedback_tickets_count',
                'has_response_tickets_count',
                'ticket_average_time_total',
                'ticket_average_time_thirty',
                'ticket_average_time_seven',
            ));
    }

    public function index2()
    {
        $tickets_count = Ticket::count();

        $ticketService = new TicketsService();

        $ticket_first_response_time_average = $ticketService->getFirstResponseTimeAverage();
        $ticket_response_time_average = $ticketService->getResponseTimeAverage();
        $average_tickets_per_day = $ticketService->getAverageTicketsPerDay();
        $average_tickets_per_week = $ticketService->getAverageTicketsPerWeek();
        $average_no_of_interactions = $ticketService->getAverageNoOfInteractions();
        $average_resolution_time = $ticketService->getAverageResolutionTime();

        return view(
            'ticketit::admin.index2',
            compact(
                'tickets_count',
                'ticket_first_response_time_average',
                'ticket_response_time_average',
                'average_tickets_per_day',
                'average_tickets_per_week',
                'average_no_of_interactions',
                'average_resolution_time'
            ));
    }
}
                          