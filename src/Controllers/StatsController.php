<?php

namespace Kordy\Ticketit\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
// use Kordy\Ticketit\Models\TSetting;
use Sentinel;
use DB;

use Kordy\Ticketit\Services\StatsService;

class StatsController extends Controller
{
    public function __construct()
    {
        // $this->middleware('Kordy\Ticketit\Middleware\IsAdminMiddleware', ['only' => ['saveOverdueHours']]);
    }

    public function index(StatsService $ss)
    {
        dd($ss->getStatuses());
    }

    public function getStatusCount(StatsService $ss)
    {
        return $ss->getStatuses();
    }

    public function getCategoriesCount(StatsService $ss)
    {
        return $ss->getCategories();
    }
}