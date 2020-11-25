<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Kordy\Ticketit\Models\TSetting;
use Sentinel;
use DB;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('Kordy\Ticketit\Middleware\IsAdminMiddleware', ['only' => ['saveOverdueHours']]);
    }

    public function index()
    {
        return view('ticketit::admin.settings.index');
    }

    public function saveOverdueHours(Request $request)
    {
        TSetting::updateOrCreate(
            ['slug' => 'overdue_hours'],
            ['slug' => 'overdue_hours', 'value' => $request->overdue_hours, 'default' => $request->overdue_hours]
        );

        session()->flash('status', 'Overdue hours updated successfully!');

        return redirect()->back();
    }
}