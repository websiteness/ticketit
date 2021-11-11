<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Sentinel;
use Kordy\Ticketit\Models\Status;
use Kordy\Ticketit\Services\TicketsService;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('Kordy\Ticketit\Middleware\IsAdminMiddleware', ['only' => ['saveOverdueHours']]);
    }

    public function index()
    {
        $statuses = Status::all()->pluck('name', 'id');
        $status_id = TSetting::where('slug', 't_setting_ticket_status_to_check')->first();
        return view('ticketit::admin.settings.index', compact('statuses', 'status_id'));
    }

    public function saveOverdueHours(Request $request)
    {
        TSetting::updateOrCreate(
            ['slug' => 'overdue_hours'],
            ['slug' => 'overdue_hours', 'value' => $request->overdue_hours, 'default' => $request->overdue_hours]
        );
        TSetting::updateOrCreate(
            ['slug' => 't_setting_ticket_status_to_check'],
            ['slug' => 't_setting_ticket_status_to_check', 'value' => $request->t_setting_ticket_status_to_check, 'default' => $request->t_setting_ticket_status_to_check]
        );

        TSetting::updateOrCreate(
            ['slug' => 'closed_days'],
            ['slug' => 'closed_days', 'value' => $request->closed_days, 'default' => $request->closed_days]
        );

        session()->flash('status', 'Overdue hours updated successfully!');

        return redirect()->back();
    }

    public function storeToken(Request $request)    
    {
        TSetting::updateOrCreate(
            ['slug' => 'api_token'],
            ['slug' => 'api_token', 'value' => $request->api_token, 'default' => $request->api_token]
        );

        session()->flash('status', 'Token successfully added!');

        return redirect()->back();
    }

    public function storeEmailReportSettings(Request $request)
    {


     
        TSetting::updateOrCreate(
            ['slug' => 'email_report_email'],
            ['slug' => 'email_report_email', 'value' => $request->email_report_email, 'default' => $request->email_report_email]
        );
        
        TSetting::updateOrCreate(
            ['slug' => 'email_report_frequency'],
            ['slug' => 'email_report_frequency', 'value' => $request->email_report_frequency, 'default' => $request->email_report_frequency]
        );
    

        TSetting::updateOrCreate(
            ['slug' => 'email_report_time'],
            ['slug' => 'email_report_time', 'value' => $request->email_report_time, 'default' => $request->email_report_time]
        );




        session()->flash('status', 'Email report settings updated successfully!');

        return redirect()->back();             
    }
}