<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Sentinel;
use DB;
use Kordy\Ticketit\Models\Status;

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
}