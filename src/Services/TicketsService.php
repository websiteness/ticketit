<?php

namespace Kordy\Ticketit\Services;

use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Models\TSetting;
use Carbon\Carbon;
use Kordy\Ticketit\Models\Status;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
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

        foreach ($data as $ticket) {
            $ticket_date = Carbon::parse($ticket->created_at);
            $interval =  $ticket_date->diffInMinutes($ticket->comment()->created_at);
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

            return $total;

        } else {
            return 0;
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

        //$ticket_count = count($data);
        $ticket_count = $total_response;

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

            return $total;

        } else {
            return 0;
        }
    }

    public function getAverageTicketsPerDay()
    {
        $tickets = Ticket::select('created_at', \DB::raw('count(id) AS total_record'))
            //->groupBy('created_at')
            ->groupByRaw('DATE(created_at)')
            ->get();

        $average_tickets_per_day = $tickets->avg('total_record') ?? '0.00';

        return $average_tickets_per_day;

    }

    public function getAverageTicketsPerWeek(){

        $tickets = Ticket::select( \DB::raw(' week(created_at)'), \DB::raw('count(id) AS total_record'))
            //->groupBy('created_at')
            ->groupByRaw('week(created_at)')
            ->get();

        $average_tickets_per_week = $tickets->avg('total_record') ?? '0.00';

        return $average_tickets_per_week;

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

            return $total;

        } else {
            return 0;
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
                    
              
}                       
                                           

