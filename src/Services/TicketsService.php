<?php

namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Models\TSetting;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Status;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Kordy\Ticketit\Repositories\CommentsRepository;
use Kordy\Ticketit\Repositories\SettingsRepository;
use App\User;
use Kordy\Ticketit\Mail\SendClosedTickets;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use DateTime;
use App\Jobs\ProcessEmailReport;

class TicketsService
{

    public function closeTicket()
    {
        $user = User::where('ticketit_admin', 1)->first();
        Sentinel::login($user);
        $status = TSetting::where('slug', 't_setting_ticket_status_to_check')->first();
        $num_of_days = TSetting::where('slug', 'closed_days')->first();
        $tickets = Ticket::where('status_id', $status->value)->where('updated_at', '<', now()->subDays($num_of_days->value)->toDateTimeString())->get();
        $closed_ticket_status_id = Status::where('name', 'like', '%Ticket Closed%')->first();
        foreach ($tickets as $ticket) {
            $ticket->status_id = $closed_ticket_status_id->id;
            $updated_ticket = $ticket->save();
            $this->sendMailToTicketOwners($ticket->user_id, $updated_ticket);
        }
    }


    public function sendMailToTicketOwners($user_id, $ticket)
    {
        $user = User::where('id', $user_id)->first();
        $template = 'ticketit::emails.closeticket';
        try {
            $mail = new SendClosedTickets($template, $user, $ticket);
            Mail::to($user->email)->queue($mail);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    public function updateInProgressTicketsToWaitingOnSupport()
    {
        Ticket::where('created_at', '>=', Carbon::now()->subDay())->where('status_id', 3)->where('completed_at', null)->update(['status_id' => 1]);
    }

    public function getOpenTicketCount() //active tickets
    {
        $closed_ticket_status = Status::where('name', 'like', '%Ticket Closed%')->first();
        $count = Ticket::where('status_id', '!=', $closed_ticket_status->id)->whereNull('completed_at')->count();

        return $count;
    }

    public function getNoResponseTicketCount() //active tickets
    {
        $closed_ticket_status = Status::where('name', 'like', '%Ticket Closed%')->first();
        $count = Ticket::whereDoesntHave('comments', function($query) {
            $query->where('ticketit_comments.user_id', '!=', 'ticketit.user_id');
        })->where('status_id', '!=', $closed_ticket_status->id)->whereNull('completed_at')->count();

        return $count;
    }

    public function getInProgressTicketCount() //active tickets
    {
        $in_progress_ticket_status = Status::where('name', 'like', '%In Progress%')->first();
        $count = Ticket::where('status_id', $in_progress_ticket_status->id)->whereNull('completed_at')->count();

        return $count;
    }

    public function getWaitingOnFeedbackCount() //active tickets
    {
        $waiting_feedback_ticket_status = Status::where('name', 'like', '%Need Feedback%')->first();
        $count = Ticket::where('status_id', $waiting_feedback_ticket_status->id)->whereNull('completed_at')->count();

        return $count;
    }

    public function getTicketsWhereUserHasResponse()  //active tickets
    {
        $closed_ticket_status = Status::where('name', 'like', '%Ticket Closed%')->first();
        $has_response = Ticket::where('ticketit.user_id', function($query) {
            return $query->from('ticketit_comments')->select('ticketit_comments.user_id')->whereColumn('ticketit_comments.ticket_id', 'ticketit.id')->orderBy('ticketit_comments.id', 'desc')->limit(1);
        })->where('status_id', '!=', $closed_ticket_status->id)->whereNull('completed_at')->count();

        return $has_response;
    }


    //Sum of First Response Times / Number of Tickets = Average First Response Time

    public function getTotalAverageResponse()
    {
        $data = Ticket::with('comments')->whereHas('comments')->get();
        $total_minutes = 0;

        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $interval =  $ticket_date->diffInMinutes($ticket->comment()->created_at);
            $total_minutes += $interval;
        }

        $ticket_count = count($data);
        
        if($ticket_count != 0 || $total_minutes != 0) {
            $hours = floor($total_minutes / 60);
            $average_total = $hours / $ticket_count;
            return $average_total;
        } else {
            return 0;
        }
    }

    public function getFirstResponseTimeAverage($filter_arr = array())
    {
        $query = Ticket::with('comments')->whereHas('comments');

        if(isset($filter_arr['user_id']) && intval($filter_arr['user_id'])>0){
            $query->where('user_id',intval($filter_arr['user_id']));
        }

        $data = $query->get();

        $total_minutes = 0;

        $total_response=0;

        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $need_response=1;

            foreach($ticket->comments as $comment){
                if($need_response==1 && $comment->user_id!=$ticket->user_id){
                    $interval =  $ticket_date->diffInMinutes($comment->created_at);
                    $need_response=0;
                    $total_minutes += $interval;
                    $total_response++;
                }

            }
        }

        //$ticket_count = count($data);

        if($total_response != 0 || $total_minutes != 0) {

            $average_total = $total_minutes / $total_response;

            $total = '';

            if(intdiv($average_total, 60)>1)
            {
                $total = intdiv($average_total, 60) . ' hours ';
            }
            else if(intdiv($average_total, 60)>0)
            {
                $total = intdiv($average_total, 60) . ' hour ';
            }

            if(($average_total % 60) > 1) {
                $total .= ($average_total % 60) . ' minutes';
            }
            else if(($average_total % 60) > 0)
            {
                $total .= ($average_total % 60) . ' minute ';
            }

            return $total ? $total : 'N/A';

        } else {
            return 'N/A';
        }
    }

    public function getResponseTimeAverage($filter_arr = array())
    {
        $query = Ticket::with('comments')->whereHas('comments');

        if(isset($filter_arr['user_id']) && intval($filter_arr['user_id'])>0){
            $query->where('user_id',intval($filter_arr['user_id']));
        }

        $data = $query->get();

        $total_minutes = 0;

        $total_response=0;

        foreach ($data as $ticket) {

            $ticket_date = Carbon::parse($ticket->created_at);
            $need_response=1;

            foreach($ticket->comments as $comment){
                if($need_response==1 && $comment->user_id!=$ticket->user_id){
                    $interval =  $ticket_date->diffInMinutes($comment->created_at);
                    $need_response=0;
                    $total_minutes += $interval;
                    $total_response++;
                }
                elseif($need_response==0 && $comment->user_id==$ticket->user_id){
                    $ticket_date = Carbon::parse($comment->created_at);
                    $need_response=1;
                }
            }
        }

        if ($total_response != 0 || $total_minutes != 0) {

            $average_total = $total_minutes / $total_response;

            $total = '';

            if(intdiv($average_total, 60)>1)
            {
                $total = intdiv($average_total, 60) . ' hours ';
            }
            else if(intdiv($average_total, 60)>0)
            {
                $total = intdiv($average_total, 60) . ' hour ';
            }

            if(($average_total % 60) > 1) {
                $total .= ($average_total % 60) . ' minutes';
            }
            else if(($average_total % 60) > 0)
            {
                $total .= ($average_total % 60) . ' minute ';
            }

            return $total ? $total : 'N/A';

        } else {
            return 'N/A';
        }
    }

    public function getAverageTicketsPerDay()
    {
        $tickets = Ticket::select('created_at', \DB::raw('count(id) AS total_record'))
            //->groupBy('created_at')
            ->groupByRaw('DATE(created_at)')
            ->get();

        $average_tickets_per_day = $tickets->avg('total_record') ?? '0.00';

        return round($average_tickets_per_day, 2);

    }

    public function getAverageTicketsPerWeek(){

        $tickets = Ticket::select( \DB::raw(' week(created_at)'), \DB::raw('count(id) AS total_record'))
            //->groupBy('created_at')
            ->groupByRaw('week(created_at)')
            ->get();

        $average_tickets_per_week = $tickets->avg('total_record') ?? '0.00';

        return round($average_tickets_per_week, 2);

    }

    public function getAverageNoOfInteractions($filter_arr = array()){

        $closed_ticket_status = Status::where('name', 'like', '%Ticket Closed%')->first();

        $query = Ticket::with('comments');

        if(isset($filter_arr['user_id']) && intval($filter_arr['user_id'])>0){
            $query->where('user_id',intval($filter_arr['user_id']));
        }

        //$query->where('status_id', '=', $closed_ticket_status->id)->whereNotNull('completed_at');


        $data = $query->get();

        $total_interactions=0;

        foreach ($data as $ticket) {
            $ticket_interaction =1;
            $comments =  $ticket->comments;
            foreach ($comments as $comment) {
                $ticket_interaction++;
            }

            $total_interactions+=$ticket_interaction;
        }

        $ticket_count = count($data);

        $average=0;

        if($ticket_count != 0) {

            $average = $total_interactions / $ticket_count;
        }

        return round($average,2);

    }

    public function getAverageResolutionTime($filter_arr = array()){

        $closed_ticket_status = Status::where('name', 'like', '%Ticket Closed%')->first();

        $query = Ticket::with('comments');

        if(isset($filter_arr['user_id']) && intval($filter_arr['user_id'])>0){
            $query->where('user_id',intval($filter_arr['user_id']));
        }

        $query->where('status_id', '=', $closed_ticket_status->id)->whereNotNull('completed_at');

        $data = $query->get();

        $total_minutes = 0;

        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $interval =  $ticket_date->diffInMinutes($ticket->completed_at);
            $total_minutes += $interval;
        }

        $ticket_count = count($data);

        if($ticket_count != 0 || $total_minutes != 0) {

            $average_total = $total_minutes / $ticket_count;

            $total = '';

            if(intdiv($average_total, 60)>1)
            {
                $total = intdiv($average_total, 60) . ' hours ';
            }
            else if(intdiv($average_total, 60)>0)
            {
                $total = intdiv($average_total, 60) . ' hour ';
            }

            if(($average_total % 60) > 1) {
                $total .= ($average_total % 60) . ' minutes';
            }
            else if(($average_total % 60) > 0)
            {
                $total .= ($average_total % 60) . ' minute ';
            }

            return $total ? $total : 'N/A';

        } else {
            return 'N/A';
        }

    }

    public function getTotalTickets($filter_arr = array()){

        $query = Ticket::query();

        if(isset($filter_arr['user_id']) && intval($filter_arr['user_id'])>0){
            $query->where('user_id',intval($filter_arr['user_id']));
        }

        $data = $query->get();

        $ticket_count = count($data);

        return $ticket_count;

    }

    public function getTotalAverageResponseThirtyDays()
    {
        $data = $this->getTicketsByDays(30);
        $total_minutes = 0;

        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $interval =  $ticket_date->diffInMinutes($ticket->comment()->created_at);
            $total_minutes += $interval;
        }

        $ticket_count = count($data);

        if($ticket_count != 0 || $total_minutes != 0) {
            $average_total = $total_minutes / $ticket_count;
            $total = '';
            if ($average_total > 1 && ($average_total % 60) > 1) {
                $total = intdiv($average_total, 60) . ' hours ' . ($average_total % 60) . ' minutes';
            } else {
                $total = intdiv($average_total, 60) . ' hour ' . ($average_total % 60) . ' minute';
            }
            return $total;
        } else {
            return 0;
        }
     
    }

    public function getTotalAverageResponseSevenDays()
    {
        $data = $this->getTicketsByDays(7);
        $total_minutes = 0;

        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $interval =  $ticket_date->diffInMinutes($ticket->comment()->created_at);
            $total_minutes += $interval;
        }

        $ticket_count = count($data);
        if($ticket_count != 0 || $total_minutes != 0) {
            $average_total = $total_minutes / $ticket_count;
            $total = '';
            if ($average_total > 1 && ($average_total % 60) > 1) {
                $total = intdiv($average_total, 60) . ' hours ' . ($average_total % 60) . ' minutes';
            } else {
                $total = intdiv($average_total, 60) . ' hour ' . ($average_total % 60) . ' minute';
            }
            return $total;
        } else {
            return 0;
        }
    }


    public function getTicketsByDays($no_of_days)
    {
        $data = Ticket::with('comments')->whereHas('comments')->where('created_at', '>=', Carbon::now()->subDays($no_of_days)->toDateTimeString())->get();
        return $data;
    }

    public function dispatchEmailReport()
    {
        try {
            $frequency = TSetting::getBySlug('email_report_frequency')->value;
            $time = TSetting::getBySlug('email_report_time')->value;
            $email = TSetting::getBySlug('email_report_email')->value; 
            //convert time to minutes
            $time = explode(':', $time);
            $time_in_minutes = ($time[0]*60) + ($time[1]);

            $emails = explode(',', $email);

            $date_today = Carbon::now();
            $today = $date_today->format('Y-m-d');

            if($frequency == "Daily"){
                ProcessEmailReport::dispatch($emails)->delay(now()->addMinutes($time_in_minutes));
            }

            if($frequency == "Weekly") {
                //check if current day is first day of the week
                $first_day_of_week = $date_today->startOfWeek()->format('Y-m-d');

                if($today == $first_day_of_week) {
                    ProcessEmailReport::dispatch($emails)->delay(now()->addMinutes($time_in_minutes));
                }
            }
            
            if($frequency == "Monthly") {
                $first_day_of_month = $date_today->startOfMonth()->format('Y-m-d');

                if($today == $first_day_of_month) {
                    ProcessEmailReport::dispatch($emails)->delay(now()->addMinutes($time_in_minutes));
                }
            }

        } catch (\Exception $e) {
            \Log::info('Tickets Error: Failed to dispatch email report');
            \Log::info($e->getMessage());
        }
    }

    public function getAverageByDateRange($date_to, $date_from)
    {
        $data = Ticket::with('comments')->whereHas('comments')->where('created_at', '>=', $date_from." 00:00:00")->where('created_at', '<=', $date_to." 00:00:00")->get();
        $total_minutes = 0;
        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $interval =  $ticket_date->diffInMinutes($ticket->comment()->created_at);
            $total_minutes += $interval;
        }

        $ticket_count = count($data);
        if($ticket_count != 0 || $total_minutes != 0) {
            $average_total = $total_minutes / $ticket_count;
            $total = '';
            if ($average_total > 1 && ($average_total % 60) > 1) {
                $total = intdiv($average_total, 60) . ' hours ' . ($average_total % 60) . ' minutes';
            } else {
                $total = intdiv($average_total, 60) . ' hour ' . ($average_total % 60) . ' minute';
            }
            return $total;
        } else {
            return 0;
        }
    }

    public function saveFiltersInSession($request)
    {

        session([
            'ticket_filter_tag_ids' => '',
            'ticket_filter_user_id' => '',
            'ticket_filter_ticket_number' => '',
            'ticket_filter_ticket_subject' => '',
            'ticket_filter_ticket_status_id' => '',
            'ticket_filter_ticket_priority_id' => '',
            'ticket_filter_ticket_date_range_type' => '',
            'ticket_filter_ticket_date_range_start' => '',
            'ticket_filter_ticket_date_range_end' => '',
        ]);

        // save the filter so we can save it when user leaves the page
        if (isset($request->custom_filters['hdn_ticket_filter_tag_ids']) && is_array($request->custom_filters['hdn_ticket_filter_tag_ids']) && count($request->custom_filters['hdn_ticket_filter_tag_ids'])) {

            $tag_ids = $request->custom_filters['hdn_ticket_filter_tag_ids'];
            session(['ticket_filter_tag_ids' => $tag_ids]);

        }

        if (isset($request->custom_filters['hdn_ticket_filter_user_id']) && $request->custom_filters['hdn_ticket_filter_user_id']) {

            $user_id = $request->custom_filters['hdn_ticket_filter_user_id'];
            session(['ticket_filter_user_id' => $user_id]);

        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_number']) && $request->custom_filters['hdn_ticket_filter_ticket_number']) {

            $ticket_number = $request->custom_filters['hdn_ticket_filter_ticket_number'];
            session(['ticket_filter_ticket_number' => $ticket_number]);

        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_subject']) && $request->custom_filters['hdn_ticket_filter_ticket_subject']) {

            $ticket_subject = $request->custom_filters['hdn_ticket_filter_ticket_subject'];
            session(['ticket_filter_ticket_subject' => $ticket_subject]);

        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_status_id']) && $request->custom_filters['hdn_ticket_filter_ticket_status_id']) {

            $ticket_status_id = $request->custom_filters['hdn_ticket_filter_ticket_status_id'];
            session(['ticket_filter_ticket_status_id' => $ticket_status_id]);

        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_priority_id']) && $request->custom_filters['hdn_ticket_filter_ticket_priority_id']) {

            $ticket_priority_id = $request->custom_filters['hdn_ticket_filter_ticket_priority_id'];
            session(['ticket_filter_ticket_priority_id' => $ticket_priority_id]);

        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_date_range_type']) && $request->custom_filters['hdn_ticket_filter_ticket_date_range_type']) {

            $ticket_date_range_type = $request->custom_filters['hdn_ticket_filter_ticket_date_range_type'];
            session(['ticket_filter_ticket_date_range_type' => $ticket_date_range_type]);

            if($ticket_date_range_type=='Custom'){
                if (
                    isset($request->custom_filters['ticket_filter_ticket_date_range_start']) && $request->custom_filters['ticket_filter_ticket_date_range_start'] &&
                    isset($request->custom_filters['ticket_filter_ticket_date_range_end']) && $request->custom_filters['ticket_filter_ticket_date_range_end']
                ) {
                    session(['ticket_filter_ticket_date_range_start' => $request->custom_filters['ticket_filter_ticket_date_range_start']]);
                    session(['ticket_filter_ticket_date_range_end' => $request->custom_filters['ticket_filter_ticket_date_range_end']]);
                }
            }

        }
    }

    public function buildDatatableQuery($user, $complete, $request)
    {

        if ($user->isAdmin()) {
            if ($complete) {
                $tickets_query = Ticket::complete()->adminUserTickets($user->id, true);
            } else {
                $tickets_query = Ticket::active()->adminUserTickets($user->id, true);

            }
        } elseif ($user->isAgent()) {
            if ($complete) {
                // $collection = Ticket::complete()->agentUserTickets($user->id);
                $tickets_query = Ticket::complete()->adminUserTickets($user->id, true);
            } else {
                // $collection = Ticket::active()->agentUserTickets($user->id);
                $tickets_query = Ticket::active()->adminUserTickets($user->id, true);
            }
        } else {
            if ($complete) {
                $tickets_query = Ticket::userTickets($user->id)->complete();
            } else {
                $tickets_query = Ticket::userTickets($user->id)->active();
            }
        }

        $tickets_query
            ->join('users', 'users.id', '=', 'ticketit.user_id')
            ->leftjoin('ticketit_statuses', 'ticketit_statuses.id', '=', 'ticketit.status_id')
            ->leftjoin('ticketit_priorities', 'ticketit_priorities.id', '=', 'ticketit.priority_id')
            ->leftjoin('ticketit_categories', 'ticketit_categories.id', '=', 'ticketit.category_id')
            ->leftjoin('tickets_developer_status', 'tickets_developer_status.id', '=', 'ticketit.dev_status_id')
            ->leftjoin('ticketit_categories AS ticketit_zone', 'ticketit_zone.id', '=', 'ticketit.zone_id')
            ->leftjoin('ticketit_ticket_tags as ttt', 'ttt.ticket_id', '=', 'ticketit.id')
            ->leftjoin('ticketit_tags as tt', 'ttt.ticketit_tags_id', '=', 'tt.id')
            ->leftJoin('ticketit_comments', function ($join) {
                $join->on('ticketit_comments.ticket_id', '=', 'ticketit.id')
                    ->whereRaw('ticketit_comments.user_id <> ticketit.user_id');
            })
            ->select([
                'ticketit.id',
                'ticketit.user_id',
                'ticketit.status_id',
                'ticketit.subject AS subject',
                'ticketit_statuses.name AS status',
                'ticketit_statuses.color AS color_status',
                'ticketit_priorities.color AS color_priority',
                'ticketit_categories.color AS color_category',
                'ticketit.id AS agent',
                'ticketit.updated_at AS updated_at',
                'ticketit_priorities.name AS priority',
                'ticketit_zone.name AS zone',
                // 'users.name AS owner',
                \DB::raw('CONCAT(users.first_name ," ", users.last_name) as owner'),
                'ticketit.agent_id',
                'ticketit_categories.name AS category',
                'tickets_developer_status.name AS developer_status',
                'ticketit.created_at AS created_at',
                'ticketit.completed_at AS completed_at',
                \DB::raw('COUNT(ticketit_comments.ticket_id) AS total_support_staff_comments')
            ])
            ->groupBy('ticketit.id');

        // check if filters are applied
        if (isset($request->custom_filters['hdn_ticket_filter_tag_ids']) && is_array($request->custom_filters['hdn_ticket_filter_tag_ids']) && $request->custom_filters['hdn_ticket_filter_tag_ids']) {
            $tag_ids = $request->custom_filters['hdn_ticket_filter_tag_ids'];
            $tickets_query->whereIn('tt.id', $tag_ids);
        }

        if (isset($request->custom_filters['hdn_ticket_filter_user_id']) && $request->custom_filters['hdn_ticket_filter_user_id']) {
            $tickets_query->where('ticketit.user_id', $request->custom_filters['hdn_ticket_filter_user_id']);
        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_number']) && $request->custom_filters['hdn_ticket_filter_ticket_number']) {
            $tickets_query->where('ticketit.id', $request->custom_filters['hdn_ticket_filter_ticket_number']);
        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_subject']) && $request->custom_filters['hdn_ticket_filter_ticket_subject']) {
            $tickets_query->where('ticketit.subject', 'like', '%' . $request->custom_filters['hdn_ticket_filter_ticket_subject'] . '%');
        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_status_id']) && $request->custom_filters['hdn_ticket_filter_ticket_status_id']) {
            if ($request->custom_filters['hdn_ticket_filter_ticket_status_id'] == 'no_response') {
                $tickets_query->whereDoesntHave('comments', function ($query) {
                    $query->where('ticketit_comments.user_id', '!=', 'ticketit.user_id');
                });
            } elseif ($request->custom_filters['hdn_ticket_filter_ticket_status_id'] == 'overdue') {
                $settings_repository = new SettingsRepository;
                $overdue_hours = $settings_repository->getOverdueHours();
                $datetime_now = \Carbon\Carbon::now()->subHours($overdue_hours);

                $tickets_query->where('ticketit.created_at', '<', $datetime_now)->whereDoesntHave('comments');
            } else {
                $tickets_query->where('ticketit.status_id', $request->custom_filters['hdn_ticket_filter_ticket_status_id']);
            }
        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_priority_id']) && $request->custom_filters['hdn_ticket_filter_ticket_priority_id']) {
            $tickets_query->where('ticketit.priority_id', $request->custom_filters['hdn_ticket_filter_ticket_priority_id']);
        }

        if (isset($request->custom_filters['hdn_ticket_filter_ticket_date_range_type']) && $request->custom_filters['hdn_ticket_filter_ticket_date_range_type']) {

            //print_r($request->custom_filters);
            $carbon = new \Carbon\Carbon(now());
            //$carbon->setTimezone($timezone);
            $ticket_date_range_type = $request->custom_filters['hdn_ticket_filter_ticket_date_range_type'];

            //$ticket_date_range_type='Last Month';
            if ($ticket_date_range_type == 'Custom') {
                if (
                    isset($request->custom_filters['hdn_ticket_filter_ticket_date_range_start']) && $request->custom_filters['hdn_ticket_filter_ticket_date_range_start'] &&
                    isset($request->custom_filters['hdn_ticket_filter_ticket_date_range_end']) && $request->custom_filters['hdn_ticket_filter_ticket_date_range_end']
                ) {

                    $start = new \Carbon\Carbon($request->custom_filters['hdn_ticket_filter_ticket_date_range_start']);
                    //$start->setTimezone($timezone);
                    $start->startOfDay()->subSeconds(1);
                    $start = $start->format('Y-m-d H:i:s');

                    $end = new \Carbon\Carbon($request->custom_filters['hdn_ticket_filter_ticket_date_range_end']);
                    //$end->setTimezone($timezone);
                    $end->endOfDay();
                    $end = $end->format('Y-m-d H:i:s');
                }
            } else if ($ticket_date_range_type == 'Today') {
                $start = new \Carbon\Carbon(now());
                $start->startOfDay()->subSeconds(1);
                $start = $start->format('Y-m-d H:i:s');

                $end = new \Carbon\Carbon(now());
                $end->endOfDay();
                $end = $end->format('Y-m-d H:i:s');
            }
            else if ($ticket_date_range_type == 'Yesterday') {
                $start = new \Carbon\Carbon(now());
                $start = $start->subDays(1);
                $start->startOfDay()->subSeconds(1);
                $start = $start->format('Y-m-d H:i:s');
                $end = new \Carbon\Carbon(now());
                $end = $end->subDays(1);
                $end->endOfDay();
                $end = $end->format('Y-m-d H:i:s');
            }
            else if ($ticket_date_range_type == 'Last 7 Days') {
                $carbon_obj = \Carbon\Carbon::now();
                $end_obj = clone $carbon_obj;
                $end = $end_obj->format('Y-m-d  H:i:s');
                $start_obj = clone $carbon_obj->subDays(7);
                $start = $start_obj->format('Y-m-d  H:i:s');
            }
            else if ($ticket_date_range_type == 'Last 30 Days') {
                $carbon_obj = \Carbon\Carbon::now();
                $end_obj = clone $carbon_obj;
                $end = $end_obj->format('Y-m-d  H:i:s');
                $start_obj = clone $carbon_obj->subDays(30);
                $start = $start_obj->format('Y-m-d  H:i:s');
            }
            else if ($ticket_date_range_type == 'This Month') {
                $start = \Carbon\Carbon::now()->startOfMonth()->subSeconds(1);
                $end = \Carbon\Carbon::now()->endOfMonth();
            }
            else if ($ticket_date_range_type == 'Last Month') {
                $start = new Carbon('first day of last month');
                $start->startOfDay()->subSeconds(1);
                $end = new Carbon('last day of last month');
                $end->endOfDay();
            }

            $tickets_query->whereBetween('ticketit.created_at', [$start, $end]);
        }

        if ($request->sub_category) {
            $tickets_query->where('ticketit.category_id', $request->sub_category);
        }

        if ($request->message) {
            $message = str_replace('}}', ' ', str_replace('{{', ' ', $request->message));

            $tickets_query->where('ticketit.html', 'like', '%' . $message . '%');
        }

        if ($request->filter_hide_closed_tickets) {
            $tickets_query->where('ticketit.status_id', '!=', 4);
        }

        if ($request->last_reply) {
            if ($request->last_reply == 'user') {
                $tickets_query->where(function ($query) {
                    $query->where('ticketit.user_id', function ($query) {
                        return $query->from('ticketit_comments')->select('ticketit_comments.user_id')->whereColumn('ticketit_comments.ticket_id', 'ticketit.id')->orderBy('ticketit_comments.id', 'desc')->limit(1);
                    })
                        ->orWhereDoesntHave('comments');
                });
            } else {
                $tickets_query->where('ticketit.user_id', '!=', function ($query) {
                    return $query->from('ticketit_comments')->select('ticketit_comments.user_id')->whereColumn('ticketit_comments.ticket_id', 'ticketit.id')->orderBy('ticketit_comments.id', 'desc')->limit(1);
                });
            }
        }

        return $tickets_query;

    }


    public function renderDatatable($tickets_query, $user, $complete, $tickets)
    {

        if (LaravelVersion::min('5.4')) {
            $datatables = app(\Yajra\DataTables\DataTables::class);
        } else {
            $datatables = app(\Yajra\Datatables\Datatables::class);
        }

        $collection = $datatables->of($tickets_query);

        $collection->editColumn('subject', function ($ticket) {

            $show_route = TSetting::grab('main_route');
            return '<a class="ticket-system__subject subject-bold" href="'.url($show_route."/{$ticket->id}").'">'.Str::limit($ticket->subject, 20).'</a>';

        });

        $collection->editColumn('status', function ($ticket) {
            $color = $ticket->color_status;
            $status = e($ticket->status);

            if($ticket->status_id == 2) {
                $status = 'Waiting on feedback from ' . e($ticket->owner);
            }

            return "<div style='color: $color'>$status</div>";
        });

        $collection->editColumn('priority', function ($ticket) {
            $color = $ticket->color_priority;
            $priority = e($ticket->priority);

            return "<div style='color: $color'>$priority</div>";
        });

        $collection->editColumn('category', function ($ticket) {
            $color = $ticket->color_category;
            $category = e($ticket->category);

            return "<div style='color: $color'>$category</div>";
        });

        $collection->editColumn('agent', function ($ticket) use($tickets){
            $ticket = $tickets->find($ticket->id);

            return isset($ticket->agent)? e($ticket->agent->name):'';
        });

        $collection->editColumn('last_reply', function ($ticket) {
            $comments_repository = new CommentsRepository;
            $comment = $comments_repository->getLastCommentByTicketId($ticket->id);

            if(!$comment) {
                return 'User';
            }

            if($ticket->user_id == $comment->user_id) {
                return 'User';
            } else {
                return 'Support';
            }
        });

        $collection->editColumn('updated_at', '{!! \Carbon\Carbon::parse($updated_at)->format("m/d/Y")." (".\Carbon\Carbon::parse($updated_at)->diffForHumans().")" !!}');

        $collection->addColumn('tags', function($ticket) use($user){

            $chr_search_arr = [
                ////"&",
                ////"<",
                ////">",
                //'"',
                "'",
                //"/"
            ];

            $chr_replace_arr = [
                ////"&amp;",
                ////"&lt;",
                ////"&gt;",
                //'&quot;',
                '&#39;',
                //'&#x2F;'
            ];

            $list = '';
            $tickets = Ticket::where('id', $ticket->id)->first();
            $tags = $tickets->tags;
            $new_tags = [];
            $ticket_tag_name_arr = [];
            foreach($tags as $tag) {
                array_push($ticket_tag_name_arr, str_replace($chr_search_arr, $chr_replace_arr, $tag->name));
                //array_push($new_tags, "<span class='label label-primary ml-3'>{$tag->name}</span>" );
                $list .= "".
                "<span
                    id='".$ticket->id."-".$tag->id."-tag'
                    class='label label-primary datatable-tag-label ml-3'>
                        ".htmlspecialchars($tag->name)."
                ";

                if ($user->isAgent() || $user->isAdmin()) {
                    $list .= "" .
                        "&nbsp;&nbsp;
                        <img
                            src='" . asset('images/contacts/new/cross-hover.png') . "'
                            title='Click to remove tag'
                            class='clickable mgl-5 tag-remove'
                            data-id='" . $ticket->id . "'
                            data-tag-id='" . $tag->id . "'
                            data-name=\"" . htmlspecialchars($tag->name) . "\"
                        />
                    ";
                }

                $list .= "".
                "</span>";
            }

            $add = "<span
                class='d-inline-block tag-popup'
                data-toggle='popover'
                data-placement='bottom'
                data-id='".$ticket->id."'
                data-values='".json_encode($ticket_tag_name_arr)."'>
                <!--<i class='fa fa-plus' data-toggle='popover' aria-hidden='true'></i>-->
                <button class='ticket-system__add-tags' data-toggle='popover' aria-hidden='true'>+ Add Tags</button></div>
            </span>";

            if(!( $user->isAgent() || $user->isAdmin() )){
                $add='';
            }
            return '<div style="text-align:center"><div id="'.$ticket->id.'-tag-wrapper" class="tag-parent">'.$list.'</div>'.$add.'</div>';

        });

        $collection->addColumn('user', function($ticket) {

            $get_photo=$ticket->user->photo;
            $photo = (is_null($get_photo)) ? asset('images/profile/place-holder.png') : \Storage::disk('s3')->url($get_photo->file_name);

            $html='<div class="ticket-system__user">
                <div class="ticket-system__img" style="background-image: url('.$photo.');"></div>
                <span class="ticket-system__username">'.$ticket->user->name.'</span>
            </div>';

            return $html;
        });

        $collection->addColumn('no_follow_up_in_one_day', function($ticket) use($user) {

            if(!( $user->isAgent() || $user->isAdmin() )){
                return false;
            }

            if(!$ticket->total_support_staff_comments && !$ticket->completed_at){

                $date = Carbon::parse($ticket->created_at);
                $now = Carbon::now();
                $diff = $date->diffInDays($now);

                if($diff>0){
                    return true;
                }
            }

            return false;
        });

        $collection->addColumn('no_resolution_in_three_days', function($ticket) use($user) {

            if(!( $user->isAgent() || $user->isAdmin() )){
                return false;
            }

            if(!$ticket->completed_at){

                $date = Carbon::parse($ticket->created_at);
                $now = Carbon::now();
                $diff = $date->diffInDays($now);

                if($diff>2){
                    return true;
                }
            }

            return false;
        });

        $collection->addColumn('actions', function ($ticket) use($complete){
            if(!$complete) {
                $route = url(TSetting::grab('main_route') . "/" . $ticket->id . '/complete');
                return '<a class="btn btn-success btn-sm" href="' . $route . '"> Resolved </a>';
            }

            return '';
        });

        // method rawColumns was introduced in laravel-datatables 7, which is only compatible with >L5.4
        // in previous laravel-datatables versions escaping columns wasn't defaut
        if (LaravelVersion::min('5.4')) {
            $collection->rawColumns(['subject', 'status', 'priority', 'category', 'agent', 'zone', 'tags', 'actions', 'user']);
        }

        $table = $collection->make(true);

        return $table;
    }
                    
              
}                       
                                           

